<?php


namespace App\Listeners;


use App\Commands\Run;
use App\Services\TeampeakService;
use App\User;
use TeamSpeak3_Adapter_ServerQuery_Event;
use TeamSpeak3_Adapter_ServerQuery_Exception;
use TeamSpeak3_Helper_Signal;
use TeamSpeak3_Node_Host;

class GlobalChatListener extends AbstractListener
{
    function init()
    {
        $this->server->notifyRegister("textserver");

        TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyTextmessage", function (TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host) {
            $this->server = $host->serverGetSelected();

            $data = $event->getData();
            $service = new TeampeakService($this->server, $this->callback);

            if ($data['msg']->startsWith("!register")) {
                $this->handleRegister($data, $service);
            } else if ($data['msg']->startsWith("!update")) {
                $this->handleUpdate($data, $service);
            } else if ($data['msg']->startsWith("!unregister")) {
                $this->handleUnregister($data, $service);
            }
        });
    }

    /**
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    private function handleRegister(array $data, TeampeakService $service)
    {
        $identityId = $data['invokeruid']->toString();

        $params = explode(' ', $data['msg']->toString(), 3);

        if (isset($params[1], $params[2]) && $user = $service->registerUser($identityId, $params[1], $params[2])) {
            $service->assignServerGroups($user);
            call_user_func($this->callback, "Registration successful");
        } else {
            $this->server->clientGetByUid($identityId)->message("Registration failed. Please enter correct username and plattform.");
            call_user_func($this->callback, "Registration failed", Run::LOG_TYPE_ERROR);
        }
    }

    private function handleUpdate(array $data, TeampeakService $service)
    {
        $identityId = $data['invokeruid']->toString();

        $user = User::find($identityId);

        if ($user) {
            $stats = $service->getPlayerStats($user->name, $user->plattform);
            if ($stats) {
                $user->stats = $stats;
                $user->save();

                $service->assignServerGroups($user);
            }
        }

    }

    private function handleUnregister(array $data, TeampeakService $service)
    {
        $identityId = $data['invokeruid']->toString();

        $user = User::find($identityId);

        if ($user) {
            $user->delete();
            $service->removeServerGroups($user);
        }
    }


}
