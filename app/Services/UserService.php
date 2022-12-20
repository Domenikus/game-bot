<?php

namespace App\Services;

use App\Assignment;
use App\Game;
use App\GameUser;
use App\Services\Gateways\GameGateway;
use App\Services\Gateways\GameGatewayFactoryInterface;
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
                    if ($managedUser = User::where('identity_id', $params[2])->first()) {
                        $this->handleUnregister($managedUser);
                    }
                    break;
                case 'block':
                    if ($managedUser = User::where('identity_id', $params[2])->first()) {
                        $managedUser->blocked = true;
                        if ($managedUser->save()) {
                            if ($client = TeamspeakGateway::getClient($user->identity_id)) {
                                TeamspeakGateway::sendMessageToClient($client,
                                    'User '.$user->identity_id.' successfully blocked');
                                Log::info('User successfully blocked', ['user' => $user->identity_id]);
                            }
                        }
                    }
                    break;
                case 'unblock':
                    if ($managedUser = User::where('identity_id', $params[2])->first()) {
                        $managedUser->blocked = false;
                        if ($managedUser->save()) {
                            if ($client = TeamspeakGateway::getClient($user->identity_id)) {
                                TeamspeakGateway::sendMessageToClient($client,
                                    'User '.$user->identity_id.' successfully unblocked');
                                Log::info('User successfully unblocked', ['user' => $user->identity_id]);
                            }
                        }
                    }
                    break;
                case 'update':
                    if ($managedUser = User::where('identity_id', $params[2])->first()) {
                        $this->handleUpdate($managedUser);
                    }
                    break;
                case 'help':
                    if ($client = TeamspeakGateway::getClient($user->identity_id)) {
                        $view = view('adminHelp', ['commandPrefix' => config('teamspeak.chat_command_prefix')]);
                        $client->message($view);
                    }
            }
        }
    }

    public function handleHelp(string $identityId): void
    {
        if ($client = TeamspeakGateway::getClient($identityId)) {
            $activeGames = Game::active()->with('types')->get();
            $view = view('help', ['commandPrefix' => config('teamspeak.chat_command_prefix'), 'activeGames' => $activeGames]);
            $client->message($view);
        }
    }

    public function handleHide(User $user, array $params = []): void
    {
        if (! isset($params[1], $params[2])) {
            return;
        }

        $game = Game::with('types')->active()->where('name', $params[1])->first();
        $type = $game?->types()->where('name', $params[2])->first();

        if ($game && $type) {
            /** @var GameUser $gameUser */
            $gameUser = $user->games()->where('game_id', $game->getKey())->first()?->game_user;
            $gameUser->types()->detach([$type->getKey()]);
            TeamspeakGateway::sendMessageToUser($user, $type->game_type->label.' successfully hidden. Run [b]update[/b] command to see the changes');
        } else {
            TeamspeakGateway::sendMessageToUser($user, 'Combination of game and type is not valid');
        }
    }

    public function handleInvalid(string $identityId, array $params = []): void
    {
        if ($client = TeamspeakGateway::getClient($identityId)) {
            TeamspeakGateway::sendMessageToClient($client, 'Invalid command, check out help to list all available commands');
        }
    }

    public function handleRegister(string $identityId, array $params = []): void
    {
        if (! isset($params[1])) {
            return;
        }

        if ($game = Game::active()->where('name', $params[1])->first()) {
            $this->registerUser($game, $identityId, $params);
        }
    }

    public function handleShow(User $user, array $params = []): void
    {
        if (! isset($params[1], $params[2])) {
            return;
        }

        $game = Game::with('types')->active()->where('name', $params[1])->first();
        $type = $game?->types()->where('name', $params[2])->first();

        if ($game && $type) {
            /** @var GameUser $gameUser */
            $gameUser = $user->games()->where('game_id', $game->getKey())->first()?->game_user;
            $gameUser->types()->syncWithoutDetaching([$type->getKey()]);
            TeamspeakGateway::sendMessageToUser($user, $type->game_type->label.' successfully enabled. Run [b]update[/b] command to see the changes');
        } else {
            TeamspeakGateway::sendMessageToUser($user, 'Combination of game and type is not valid');
        }

        // Todo update server groups
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
                        Log::info('User successfully unregistered',
                            ['user' => $user->identity_id, 'game' => $game->name]);
                    }
                }
            } else {
                foreach ($user->games as $game) {
                    $assignments = $game->assignments()->get();

                    $this->removeServerGroups($game->game_user, $assignments);
                    Log::info('User successfully unregistered', ['user' => $user->identity_id, 'game' => $game->name]);
                }

                $user->delete();
            }
        }
    }

    public function handleUpdate(User $user): void
    {
        if (! $user->isBlocked()) {
            $user->loadMissing('games');

            foreach ($user->games as $game) {
                $assignments = $game->assignments()->with(['type'])->get();
                $gameGatewayFactory = App::make(GameGatewayFactoryInterface::class);
                $gateway = $gameGatewayFactory->create($game->name);
                $this->updateServerGroups($game->game_user, $assignments, $gateway);
            }
        }
    }

    public function handleUpdateAll(): void
    {
        $this->updateActiveClients();
    }

    protected function registerUser(Game $game, string $identityId, array $params): void
    {
        $client = TeamspeakGateway::getClient($identityId);
        if ($client) {
            TeamspeakGateway::sendMessageToClient($client, 'Registration in progress, please wait...');
        }

        $gameGatewayFactory = App::make(GameGatewayFactoryInterface::class);
        $gateway = $gameGatewayFactory->create($game->name);
        $options = $gateway->grabPlayerIdentity($params);
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
            if ($client) {
                TeamspeakGateway::sendMessageToClient($client, 'Registration failed, you are blocked by an admin.');
                Log::info('Blocked user tried to register', ['user' => $user->identity_id]);
            }

            return;
        }

        $user->games()->syncWithPivotValues([$game->getKey()], ['options' => $options], false);

        /** @var GameUser $gameUser */
        $gameUser = $user->games()->where('game_id', $game->getKey())->first()?->game_user;
        $gameUser->types()->sync($game->types()->pluck('id'));
        Log::info('User successfully registered', ['game' => $game->name, 'identityId' => $identityId, 'params' => $params]);

        $user->refresh();
        $assignments = $game->assignments()->with(['type'])->get();
        $gameGatewayFactory = App::make(GameGatewayFactoryInterface::class);
        $gateway = $gameGatewayFactory->create($game->name);
        $this->updateServerGroups($gameUser, $assignments, $gateway);
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

    protected function updateActiveClients(): void
    {
        foreach (TeamspeakGateway::getActiveClients() as $client) {
            if ($user = User::where('identity_id', $client['client_unique_identifier'])->first()) {
                $this->handleUpdate($user);
            }
        }
    }

    /**
     * @param  GameUser  $gameUser
     * @param  Collection<int, Assignment>  $assignments
     * @param  GameGateway  $interface
     * @return void
     */
    protected function updateServerGroups(GameUser $gameUser, Collection $assignments, GameGateway $interface): void
    {
        $stats = $interface->grabPlayerData($gameUser);
        $gameUser->loadMissing('types');
        if (! $stats) {
            return;
        }

        $newTeamspeakServerGroups = $interface->mapStats($gameUser, $stats, $assignments);
        /** @var array<int,string> $allowedTypes */
        $allowedTypes = $gameUser->types->pluck('name')->toArray();
        $typesToShow = array_intersect_key($newTeamspeakServerGroups, array_flip($allowedTypes));

        if ($client = TeamspeakGateway::getClient($gameUser->user_identity_id)) {
            $actualServerGroups = TeamspeakGateway::getServerGroupsAssignedToClient($client);
            $supportedTeamspeakServerGroupIds = $assignments->pluck('ts3_server_group_id')->toArray();

            foreach ($actualServerGroups as $actualServerGroup) {
                if (in_array($actualServerGroup, $supportedTeamspeakServerGroupIds)
                    && ! in_array($actualServerGroup, $typesToShow)) {
                    if (is_numeric($actualServerGroup)) {
                        TeamspeakGateway::removeServerGroupFromClient($client, (int) $actualServerGroup);
                    }
                }
            }

            foreach ($typesToShow as $newGroup) {
                if (! in_array($newGroup, $actualServerGroups)) {
                    TeamspeakGateway::assignServerGroupToClient($client, $newGroup);
                }
            }

            Log::info('Server groups successfully updated',
                ['identityId' => $gameUser->user_identity_id, 'gameId' => $gameUser->game_id]);
        }
    }
}
