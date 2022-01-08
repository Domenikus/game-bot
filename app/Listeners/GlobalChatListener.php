<?php


namespace App\Listeners;


use App\User;
use TeamSpeak3_Adapter_ServerQuery_Event;
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
            $identityId = $data['invokeruid']->toString();
            $user = User::find($identityId);

            $params = explode('|', $data['msg']->toString());
            if ($data['msg']->startsWith("!register")) {
                $this->handleRegister($params, $identityId);
            } else if ($data['msg']->startsWith("!update") && $user) {
                $this->handleUpdate($user);
            } else if ($data['msg']->startsWith("!unregister") && $user) {
                $this->handleUnregister($user);
            }
        });
    }
}
