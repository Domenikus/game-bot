<?php

namespace App\Services;

use App\Assignment;
use App\Game;
use App\GameUser;
use App\Services\Gateways\GameGateway;
use App\Services\Gateways\GameGatewayRegistry;
use App\Services\Gateways\TeamspeakGateway;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class UserService implements UserServiceInterface
{
    public function handleAdmin(User $user, array $params = []): void
    {
        if ($user->isAdmin() && isset($params[1])) {
            switch ($params[1]) {
                case 'unregister':
                    if ($userToUnregister = User::where('identity_id', $params[2])->first()) {
                        $this->handleUnregister($userToUnregister, []);
                    }
                    break;
                case 'block':
                    if ($userToBlock = User::where('identity_id', $params[2])->first()) {
                        $userToBlock->blocked = true;
                        if ($userToBlock->save()) {
                            if ($client = TeamspeakGateway::getClient($user->identity_id)) {
                                TeamspeakGateway::sendMessageToClient($client,
                                    'UserService '.$user->identity_id.' successfully blocked');
                                Log::info('UserService successfully blocked', ['user' => $user->identity_id]);
                            }
                        }
                    }
                    break;
                case 'unblock':
                    if ($userToUnblock = User::where('identity_id', $params[2])->first()) {
                        $userToUnblock->blocked = false;
                        if ($userToUnblock->save()) {
                            if ($client = TeamspeakGateway::getClient($user->identity_id)) {
                                TeamspeakGateway::sendMessageToClient($client,
                                    'UserService '.$user->identity_id.' successfully unblocked');
                                Log::info('UserService successfully unblocked', ['user' => $user->identity_id]);
                            }
                        }
                    }
                    break;
                case 'update':
                    $this->updateActiveClients();
                    break;
                default:
            }
        }
    }

    public function handleUnregister(User $user, array $params = []): void
    {
        $user->loadMissing('games');

        if (! $user->isBlocked()) {
            if (isset($params[1])) {
                foreach ($user->games as $game) {
                    if ($game->name == $params[1]) {
                        $assignments = $game->assignments()->get();

                        $this->removeServerGroups($game->game_user, $assignments);
                        $user->games()->detach($game->getKey());
                        Log::info('UserService successfully unregistered',
                            ['user' => $user->identity_id, 'game' => $game->name]);
                    }
                }
            } else {
                foreach ($user->games as $game) {
                    $assignments = $game->assignments()->get();

                    $this->removeServerGroups($game->game_user, $assignments);
                    Log::info('UserService successfully unregistered', ['user' => $user->identity_id, 'game' => $game->name]);
                }

                $user->delete();
            }
        }
    }

    /**
     * @param  GameUser  $gameUser
     * @param  Collection<int, Assignment>  $assignments
     * @return void
     */
    protected function removeServerGroups(GameUser $gameUser, Collection $assignments): void
    {
        if ($client = TeamspeakGateway::getClient($gameUser->user_identity_id)) {
            $actualServerGroups = $client->memberOf();
            $supportedTeamspeakServerGroupIds = $assignments->pluck('ts3_server_group_id')->toArray();
            foreach ($actualServerGroups as $actualServerGroup) {
                if (isset($actualServerGroup['sgid']) && in_array($actualServerGroup['sgid'],
                    $supportedTeamspeakServerGroupIds)) {
                    TeamspeakGateway::removeServerGroupFromClient($client, $actualServerGroup['sgid']);
                }
            }
        }
    }

    public function handleRegister(string $identityId, array $params = []): void
    {
        if (! isset($params[1])) {
            return;
        }

        if ($game = Game::where('name', $params[1])->first()) {
            $this->registerUser($game, $identityId, $params);
        }
    }

    protected function registerUser(Game $game, string $identityId, array $params): void
    {
        $registry = App::make(GameGatewayRegistry::class);
        $interface = $registry->get($game->name);

        $options = $interface->getPlayerIdentity($params);
        if (! $options) {
            if ($client = TeamspeakGateway::getClient($identityId)) {
                TeamspeakGateway::sendMessageToClient($client, 'Registration failed, please check params');
            }

            Log::info('Registration failed', ['identityId' => $identityId]);

            return;
        }

        $user = User::with('games')->find($identityId);
        if (! $user) {
            $user = new User();
            $user->identity_id = $identityId;
            $user->save();
        }

        if ($user->isBlocked()) {
            if ($client = TeamspeakGateway::getClient($identityId)) {
                TeamspeakGateway::sendMessageToClient($client, 'Registration failed, you are blocked by an admin.');
                Log::info('Blocked user tried to register', ['user' => $user->identity_id]);
            }

            return;
        }

        if (GameUser::where([['user_identity_id', $user->getKey()], ['game_id', $game->getKey()]])->first()) {
            $user->games()->updateExistingPivot($game->getKey(), ['options' => $options]);
        } else {
            $user->games()->attach($game->getKey(), ['options' => $options]);
        }

        Log::info('UserService successfully registered', ['game' => $game->name, 'identityId' => $identityId, 'params' => $params]);
        $this->handleUpdate($user->refresh());
    }

    public function handleUpdate(User $user): void
    {
        if (! $user->isBlocked()) {
            $user->loadMissing('games');

            foreach ($user->games as $game) {
                $assignments = $game->assignments()->with(['type'])->get();
                $registry = App::make(GameGatewayRegistry::class);
                $interface = $registry->get($game->name);
                $this->updateServerGroups($game->game_user, $assignments, $interface);
            }
        }
    }

    /**
     * @param  GameUser  $gameUser
     * @param  Collection<int, Assignment>  $assignments
     * @param  GameGateway  $interface
     * @return void
     */
    protected function updateServerGroups(
        GameUser $gameUser,
        Collection $assignments,
        GameGateway $interface
    ): void {
        $stats = $interface->getPlayerData($gameUser);
        if (! $stats) {
            return;
        }

        $newTeamspeakServerGroups = $interface->mapStats($gameUser, $stats, $assignments);

        if ($client = TeamspeakGateway::getClient($gameUser->user_identity_id)) {
            $actualServerGroups = TeamspeakGateway::getServerGroupsAssignedToClient($client);
            $supportedTeamspeakServerGroupIds = $assignments->pluck('ts3_server_group_id')->toArray();

            foreach ($actualServerGroups as $actualServerGroup) {
                if (in_array($actualServerGroup, $supportedTeamspeakServerGroupIds)
                    && ! in_array($actualServerGroup, $newTeamspeakServerGroups)) {
                    if (is_numeric($actualServerGroup)) {
                        TeamspeakGateway::removeServerGroupFromClient($client, (int) $actualServerGroup);
                    }
                }
            }

            foreach ($newTeamspeakServerGroups as $newGroup) {
                if (! in_array($newGroup, $actualServerGroups)) {
                    TeamspeakGateway::assignServerGroupToClient($client, $newGroup);
                }
            }

            Log::info('Server groups successfully updated',
                ['identityId' => $gameUser->user_identity_id, 'gameId' => $gameUser->game_id]);
        }
    }

    public function handleUpdateAll(): void
    {
        $this->updateActiveClients();
    }

    protected function updateActiveClients(): void
    {
        foreach (TeamspeakGateway::getActiveClients() as $client) {
            if ($user = User::where('identity_id', $client->getInfo()['client_unique_identifier'])->first()) {
                $this->handleUpdate($user);
            }
        }
    }
}
