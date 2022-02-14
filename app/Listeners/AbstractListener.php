<?php


namespace App\Listeners;

use App\Commands\Run;
use App\Game;
use App\GameUser;
use App\Interfaces\AbstractGameInterface;
use App\Interfaces\Teamspeak;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use TeamSpeak3_Adapter_ServerQuery_Exception;
use TeamSpeak3_Node_Server;

abstract class AbstractListener
{
    protected TeamSpeak3_Node_Server $server;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * AbstractListener constructor.
     * @param TeamSpeak3_Node_Server $server
     * @param callable $callback
     */
    public function __construct(TeamSpeak3_Node_Server $server, callable $callback)
    {
        $this->server = $server;
        $this->callback = $callback;
    }

    abstract function init(): void;

    /**
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    public function handleUpdate(User $user): void
    {
        if (!$user->isBlocked()) {
            $user->loadMissing('games');

            foreach ($user->games as $game) {
                $assignments = $game->assignments()->get();
                if (isset(config('game.gameInterfaces')[$game->name])) {
                    $interface = resolve(config('game.gameInterfaces')[$game->name]);
                    if (!$interface->getApiKey()) {
                        call_user_func($this->callback, 'No API key provided for ' . $game->name, Run::LOG_TYPE_ERROR);
                        return;
                    }

                    $this->updateServerGroups($game->game_user, $assignments, $interface);
                }
            }
        }
    }

    /**
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    public function handleRegister(string $identityId, array $params): void
    {
        if (isset($params[1]) && isset(config('game.gameInterfaces')[$params[1]])) {
            $interface = resolve(config('game.gameInterfaces')[$params[1]]);
            if (!$interface->getApiKey()) {
                call_user_func($this->callback, 'No API key provided for ' . $params[1], Run::LOG_TYPE_ERROR);
                return;
            }

            $this->registerUser($params, $identityId, $interface);
        }
    }

    /**
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
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
                        call_user_func($this->callback, 'User ' . $user->identity_id . ' successfully unregistered from ' . $game->name);
                    }
                }
            } else {
                foreach ($user->games as $game) {
                    $assignments = $game->assignments()->get();

                    $this->removeServerGroups($game->game_user, $assignments);
                    call_user_func($this->callback, 'User ' . $user->identity_id . ' successfully unregistered from ' . $game->name);
                }

                $user->delete();
            }
        }
    }

    /**
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
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
                                $teamspeakInterface->sendMessageToClient($client, 'User ' . $user->identity_id . ' successfully blocked');
                                call_user_func($this->callback, 'User ' . $user->identity_id . ' successfully blocked');
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
                                $teamspeakInterface->sendMessageToClient($client, 'User ' . $user->identity_id . ' successfully unblocked');
                                call_user_func($this->callback, 'User ' . $user->identity_id . ' successfully unblocked');
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

    /**
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    protected function updateServerGroups(GameUser $gameUser, Collection $assignments, AbstractGameInterface $interface): void
    {
        $stats = $interface->getPlayerData($gameUser);
        if (!$stats) {
            call_user_func($this->callback, 'Error while getting stats in ' . get_class($interface), Run::LOG_TYPE_ERROR);
            return;
        }
        $newTeamspeakServerGroups = $interface->mapStats($gameUser, $stats, $assignments);

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

            call_user_func($this->callback, 'Server groups for user ' . $gameUser->user_identity_id . ' successfully updated in ' . get_class($interface));
        }
    }

    /**
     * @param array $params
     * @param string $identityId
     * @param AbstractGameInterface $interface
     * @return void
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    protected function registerUser(array $params, string $identityId, AbstractGameInterface $interface): void
    {
        $teamspeakInterface = new Teamspeak($this->server);

        $options = $interface->getPlayerIdentity($params);
        if (!$options) {
            if ($client = $teamspeakInterface->getClient($identityId)) {
                call_user_func($this->callback, 'Registration for user ' . $identityId . ' failed, please check params', Run::LOG_TYPE_ERROR);
                $teamspeakInterface->sendMessageToClient($client, 'Registration failed, please check params');
            }

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
                call_user_func($this->callback, 'Blocked user ' . $identityId . ' tried to register');
                $teamspeakInterface->sendMessageToClient($client, 'Registration failed, you are blocked by the admin.');
            }
            return;
        }

        $game = Game::where('name', $params[1])->firstOrFail();

        if (GameUser::where([['user_identity_id', $user->getKey()], ['game_id', $game->getKey()]])->first()) {
            $user->games()->updateExistingPivot($game->getKey(), ['options' => $options]);
        } else {
            $user->games()->attach($game->getKey(), ['options' => $options]);
        }

        call_user_func($this->callback, 'User ' . $identityId . ' successfully registered in ' . get_class($interface));
        $this->handleUpdate($user->refresh());
    }

    /**
     * @param GameUser $gameUser
     * @param Collection $assignments
     */
    protected function removeServerGroups(GameUser $gameUser, Collection $assignments): void
    {
        $teamspeakInterface = new Teamspeak($this->server);

        if ($client = $teamspeakInterface->getClient($gameUser->user_identity_id)) {
            $actualServerGroups = $client->memberOf();
            $supportedTeamspeakServerGroupIds = $assignments->pluck('ts3_server_group_id')->toArray();
            foreach ($actualServerGroups as $actualServerGroup) {
                if (isset($actualServerGroup['sgid']) && in_array($actualServerGroup['sgid'], $supportedTeamspeakServerGroupIds)) {
                    $teamspeakInterface->removeServerGroup($client, $actualServerGroup['sgid']);
                }
            }
        }
    }

    /**
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    protected function updateActiveClients(): void
    {
        foreach ($this->server->clientList() as $client) {
            if ($user = User::find($client->getInfo()['client_unique_identifier']->toString())) {
                $this->handleUpdate($user);
            }
        }
    }
}
