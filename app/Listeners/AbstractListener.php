<?php


namespace App\Listeners;

use App\Game;
use App\GameUser;
use App\Interfaces\AbstractGameInterface;
use App\Interfaces\Teamspeak;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use TeamSpeak3_Node_Server;

abstract class AbstractListener
{
    protected TeamSpeak3_Node_Server $server;


    public function __construct(TeamSpeak3_Node_Server $server)
    {
        $this->server = $server;
    }

    abstract function init(): void;

    public function handleUpdate(User $user): void
    {
        if (!$user->isBlocked()) {
            $user->loadMissing('games');

            foreach ($user->games as $game) {
                $assignments = $game->assignments()->get();
                $queues = $game->queues()->with('type')->get();
                $interface = resolve($game->interface);
                $this->updateServerGroups($game->game_user, $queues, $assignments, $interface);
            }
        }
    }

    public function handleRegister(string $identityId, array $params): void
    {
        $game = Game::where('name', $params[1])->first();
        if (isset($params[1]) && $game) {
            $interface = resolve($game->interface);
            if (!$interface->getApiKey()) {
                Log::error('No API key provided, registration skipped',
                    ['game' => $game, 'apiKey' => $interface->getApiKey()]);
                return;
            }

            $this->registerUser($params, $identityId, $interface);
        }
    }

    public function handleUnregister(User $user, array $params): void
    {
        $user->loadMissing('games');

        if (!$user->isBlocked()) {
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

    public function handleAdmin(User $user, array $params): void
    {
        if ($user->isAdmin() && isset($params[1])) {
            switch ($params[1]) {
                case 'unregister':
                    if ($userToUnregister = User::find($params[2])) {
                        $this->handleUnregister($userToUnregister, []);
                    }
                    break;
                case 'block':
                    if ($userToBlock = User::find($params[2])) {
                        $userToBlock->blocked = true;
                        if ($userToBlock->save()) {
                            $teamspeakInterface = new Teamspeak($this->server);
                            if ($client = $teamspeakInterface->getClient($user->identity_id)) {
                                $teamspeakInterface->sendMessageToClient($client,
                                    'User ' . $user->identity_id . ' successfully blocked');
                                Log::info('User successfully blocked', ['user' => $user->identity_id]);
                            }
                        }
                    }
                    break;
                case 'unblock':
                    if ($userToUnblock = User::find($params[2])) {
                        $userToUnblock->blocked = false;
                        if ($userToUnblock->save()) {
                            $teamspeakInterface = new Teamspeak($this->server);
                            if ($client = $teamspeakInterface->getClient($user->identity_id)) {
                                $teamspeakInterface->sendMessageToClient($client,
                                    'User ' . $user->identity_id . ' successfully unblocked');
                                Log::info('User successfully unblocked', ['user' => $user->identity_id]);
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

    protected function updateServerGroups(
        GameUser $gameUser,
        Collection $queues,
        Collection $assignments,
        AbstractGameInterface $interface
    ): void {
        $stats = $interface->getPlayerData($gameUser);
        if (!$stats) {
            return;
        }

        $newTeamspeakServerGroups = $interface->mapStats($gameUser, $stats, $assignments, $queues);

        $teamspeakInterface = new Teamspeak($this->server);
        if ($client = $teamspeakInterface->getClient($gameUser->user_identity_id)) {
            $actualServerGroups = $client->memberOf();
            $supportedTeamspeakServerGroupIds = $assignments->pluck('ts3_server_group_id')->toArray();
            foreach ($actualServerGroups as $actualServerGroup) {
                if (isset($actualServerGroup['sgid'])
                    && in_array($actualServerGroup['sgid'], $supportedTeamspeakServerGroupIds)
                    && !in_array($actualServerGroup['sgid'], $newTeamspeakServerGroups)) {
                    $teamspeakInterface->removeServerGroup($client, $actualServerGroup['sgid']);
                }
            }

            foreach ($newTeamspeakServerGroups as $newGroup) {
                $teamspeakInterface->addServerGroup($client, $newGroup);
            }

            Log::info('Server groups successfully updated', ['gameUser' => $gameUser]);
        }
    }

    protected function registerUser(array $params, string $identityId, AbstractGameInterface $interface): void
    {
        $teamspeakInterface = new Teamspeak($this->server);

        $options = $interface->getPlayerIdentity($params);
        if (!$options) {
            if ($client = $teamspeakInterface->getClient($identityId)) {
                $teamspeakInterface->sendMessageToClient($client, 'Registration failed, please check params');
            }

            Log::info('Registration failed', ['identityId' => $identityId]);
            return;
        }

        $user = User::with('games')->find($identityId);
        if (!$user) {
            $user = new User();
            $user->identity_id = $identityId;
            $user->save();
        }

        if ($user->isBlocked()) {
            if ($client = $teamspeakInterface->getClient($identityId)) {
                $teamspeakInterface->sendMessageToClient($client, 'Registration failed, you are blocked by an admin.');
                Log::info('Blocked user tried to register', ['user' => $user->identity_id]);
            }

            return;
        }

        $game = Game::where('name', $params[1])->firstOrFail();

        if (GameUser::where([['user_identity_id', $user->getKey()], ['game_id', $game->getKey()]])->first()) {
            $user->games()->updateExistingPivot($game->getKey(), ['options' => $options]);
        } else {
            $user->games()->attach($game->getKey(), ['options' => $options]);
        }

        Log::info('User successfully registered', ['identityId' => $identityId, 'game' => $game->name]);
        $this->handleUpdate($user->refresh());
    }

    protected function removeServerGroups(GameUser $gameUser, Collection $assignments): void
    {
        $teamspeakInterface = new Teamspeak($this->server);

        if ($client = $teamspeakInterface->getClient($gameUser->user_identity_id)) {
            $actualServerGroups = $client->memberOf();
            $supportedTeamspeakServerGroupIds = $assignments->pluck('ts3_server_group_id')->toArray();
            foreach ($actualServerGroups as $actualServerGroup) {
                if (isset($actualServerGroup['sgid']) && in_array($actualServerGroup['sgid'],
                        $supportedTeamspeakServerGroupIds)) {
                    $teamspeakInterface->removeServerGroup($client, $actualServerGroup['sgid']);
                }
            }
        }
    }

    protected function updateActiveClients(): void
    {
        foreach ($this->server->clientList() as $client) {
            if ($user = User::find($client->getInfo()['client_unique_identifier']->toString())) {
                $this->handleUpdate($user);
            }
        }
    }
}
