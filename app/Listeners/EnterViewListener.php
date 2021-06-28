<?php


namespace App\Listeners;


use App\Services\TeampeakService;
use App\User;
use TeamSpeak3_Adapter_ServerQuery_Event;
use TeamSpeak3_Helper_Signal;
use TeamSpeak3_Node_Host;

class EnterViewListener extends AbstractListener
{
    function init()
    {
        $this->server->notifyRegister("server");

        TeamSpeak3_Helper_Signal::getInstance()->subscribe("notifyCliententerview", function (TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host) {
            $this->server = $host->serverGetSelected();

            $data = $event->getData();
            $identityId = $data['client_unique_identifier']->toString();
            $user = User::find($identityId);

            if ($user) {
                $service = new TeampeakService($this->server, $this->callback);
                $stats = $service->getPlayerStats($user->name, $user->plattform);
                if ($stats) {
                    $user->stats = $stats;
                    $user->save();

                    $service->assignServerGroups($user);
                }
            }
        });
    }
}
