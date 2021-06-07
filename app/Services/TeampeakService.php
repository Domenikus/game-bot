<?php

namespace App\Services;

use App\User;
use TeamSpeak3;
use TeamSpeak3_Adapter_ServerQuery_Event;
use TeamSpeak3_Helper_Signal;
use TeamSpeak3_Node_Host;
use TeamSpeak3_Node_Server;

class TeampeakService
{
    const PLATFORMS = [
        'origin',
        'xbl',
        'psn'
    ];

    /**
     * @throws \Exception
     */
    public function connect(): TeamSpeak3_Node_Server
    {
        $uri = "serverquery://"
            . config('teamspeak.query_user') . ":"
            . config('teamspeak.query_password') . "@"
            . config('teamspeak.ip') . ":"
            . config('teamspeak.query_port') . "/?server_port="
            . config('teamspeak.port') . "&blocking=0";
        return TeamSpeak3::factory($uri);
    }

    public function subscribeForRegistrations(TeamSpeak3_Node_Server &$server, callable $callback)
    {
        $server->notifyRegister("textserver");
        TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyTextmessage", function (TeamSpeak3_Adapter_ServerQuery_Event $event) use ($callback) {
            $data = $event->getData();
            if ($data['msg']->startsWith("!register")) {
                call_user_func($callback, "Register command detected");

                $params = explode(' ', $data['msg']->toString(), 3);
                $identityId = $data['invokeruid']->toString();

                if ($this->registerUser($identityId, $params[1], $params[2])) {
                    call_user_func($callback, "Register completed successfully");
                } else {
                    call_user_func($callback, "Registration failed");
                }
            }
        });
    }

    public function getPlayerStats(TeamSpeak3_Node_Server &$server)
    {
        $clients = $server->clientList();
        foreach ($clients as $client) {
            $user = User::where('identity_id', $client['client_unique_identifier']);
            if ($user) {

            }
        }
        dd($clients);
    }

    private function registerUser(string $identityId, string $name, string $plattform)
    {
        if (empty($identityId) || empty($name) || empty($plattform)) {
            return false;
        }

        if (!in_array($plattform, self::PLATFORMS)) {
            return false;
        }

        $user = User::find($identityId);
        if (!$user) {
            $user = new User();
            $user->identity_id = $identityId;
        }

        $user->name = $name;
        $user->plattform = $plattform;

        return $user->save();
    }
}
