<?php


namespace App\Listeners;


use App\Commands\Run;
use App\Game;
use App\GameUser;
use App\Interfaces\AbstractGameInterface;
use App\Interfaces\Apex;
use App\Interfaces\Lol;
use App\Interfaces\Teamspeak;
use App\User;
use Illuminate\Database\Eloquent\Collection;
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

    abstract function init();

    public function handleUpdate(User $user)
    {
        $user->loadMissing('games');

        foreach ($user->games as $game) {
            $assignments = $game->assignments()->get();

            switch ($game->name) {
                case Game::NAME_APEX:
                    $interface = resolve(Apex::class);
                    break;
                case Game::NAME_LEAGUE_OF_LEGENDS:
                    $interface = resolve(Lol::class);
                    break;
                default:
                    return;
            }

            $this->updateServerGroups($game->game_user, $assignments, $interface);
        }
    }

    public function handleRegister(string $identityId, array $params)
    {
        if (isset($params[1])) {

            switch ($params[1]) {
                case Game::NAME_APEX:
                    $interface = resolve(Apex::class);
                    break;
                case Game::NAME_LEAGUE_OF_LEGENDS:
                    $interface = resolve(Lol::class);
                    break;
                default:
                    return;
            }

            $this->registerUser($params, $identityId, $interface);
        }
    }

    public function handleUnregister(User $user, array $params)
    {
        $user->loadMissing('games');

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

    protected function updateServerGroups(GameUser $gameUser, Collection $assignments, AbstractGameInterface $interface)
    {
        $stats = $interface->getStats($gameUser);
        if (!$stats) {
            call_user_func($this->callback, 'Error while getting stats in ' . get_class($interface), Run::LOG_TYPE_ERROR);
            return;
        }
        $newTeamspeakServerGroups = $interface->mapStats($gameUser, $stats, $assignments);

        $teamspeakInterface = new Teamspeak($this->server);
        $client = $teamspeakInterface->getClient($gameUser->user_identity_id);

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

    protected function registerUser(array $params, string $identityId, AbstractGameInterface $interface)
    {
        $teamspeakInterface = new Teamspeak($this->server);

        $options = $interface->register($params);
        if (!$options) {
            call_user_func($this->callback, 'Registration for user ' . $identityId . ' failed, please check params', Run::LOG_TYPE_ERROR);
            $teamspeakInterface->sendMessageToClient($teamspeakInterface->getClient($identityId), 'Registration failed, please check params');
            return;
        }

        $user = User::with('games')->find($identityId);
        if (!$user) {
            $user = new User();
            $user->identity_id = $identityId;
            $user->save();
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

    protected function removeServerGroups(GameUser $gameUser, Collection $assignments)
    {
        $teamspeakInterface = new Teamspeak($this->server);
        $client = $teamspeakInterface->getClient($gameUser->user_identity_id);

        $actualServerGroups = $client->memberOf();
        $supportedTeamspeakServerGroupIds = $assignments->pluck('ts3_server_group_id')->toArray();
        foreach ($actualServerGroups as $actualServerGroup) {
            if (isset($actualServerGroup['sgid']) && in_array($actualServerGroup['sgid'], $supportedTeamspeakServerGroupIds)) {
                $teamspeakInterface->removeServerGroup($client, $actualServerGroup['sgid']);
            }
        }
    }
}
