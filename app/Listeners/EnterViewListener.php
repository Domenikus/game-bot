<?php


namespace App\Listeners;

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
                $this->handleUpdate($user);
            }
        });
    }
}
