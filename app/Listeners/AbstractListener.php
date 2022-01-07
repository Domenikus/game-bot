<?php


namespace App\Listeners;


use App\Assignment;
use App\Game;
use App\GameUser;
use App\Interfaces\AbstractGameInterface;
use App\Interfaces\Apex;
use App\Interfaces\Teamspeak;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use TeamSpeak3_Node_Server;

abstract class AbstractListener
{
    /**
     * @var TeamSpeak3_Node_Server
     */
    protected $server;

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
            $assignments = Assignment::with(['type', 'game' => function ($query) use ($game) {
                $query->where('name', $game->name);
            }])->get();

            switch ($game->name) {
                case Game::NAME_APEX:
                    $apexInterface = resolve(Apex::class);
                    $this->updateServerGroups($game->game_user, $assignments, $apexInterface);
                    break;
            }
        }
    }

    public function handleRegister(array $eventData)
    {
        $identityId = $eventData['invokeruid']->toString();
        $params = explode(' ', $eventData['msg']->toString());

        if (isset($params[1])) {
            switch ($params[1]) {
                case Game::NAME_APEX:
                    $apexInterface = resolve(Apex::class);
                    $this->registerUser($params, $identityId, $apexInterface);
                    break;
            }
        }
    }

    public function handleUnregister(User $user)
    {
        $user->loadMissing('games');

        foreach ($user->games as $game) {
            $assignments = Assignment::with(['type', 'game' => function ($query) use ($game) {
                $query->where('name', $game->name);
            }])->get();

            $this->removeServerGroups($game->game_user, $assignments);
        }

        $user->delete();
    }

    protected function updateServerGroups(GameUser $gameUser, Collection $assignments, AbstractGameInterface $interface)
    {
        $stats = $interface->getStats($gameUser);
        $newTeamspeakServerGroups = $interface->mapStats($stats, $assignments);

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
            call_user_func($this->callback, 'Server groups successfully updated');
        }
    }

    protected function registerUser(array $params, string $identityId, AbstractGameInterface $interface)
    {
        $teamspeakInterface = new Teamspeak($this->server);

        $options = $interface->register($params);
        if (!$options) {
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

        $this->handleUpdate($user);
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
