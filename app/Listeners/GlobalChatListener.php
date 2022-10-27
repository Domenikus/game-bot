<?php

namespace App\Listeners;

use App\User;
use TeamSpeak3_Adapter_ServerQuery_Event;
use TeamSpeak3_Adapter_ServerQuery_Exception;
use TeamSpeak3_Helper_Signal;
use TeamSpeak3_Node_Host;

class GlobalChatListener extends AbstractListener
{
    public function init(): void
    {
        $this->server->notifyRegister('textserver');

        TeamSpeak3_Helper_Signal::getInstance()->subscribe('notifyTextmessage',
            function (TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host) {
                $this->handle($event, $host);
            });
    }

    /**
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    protected function handle(TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host): void
    {
        $this->server = $host->serverGetSelected();

        $data = $event->getData();
        $identityId = $data['invokeruid']->toString();
        $user = User::where('identity_id', $identityId)->first();

        $params = explode('|', $data['msg']->toString());
        if ($data['msg']->startsWith('!register')) {
            $this->handleRegister($identityId, $params);
        } elseif ($data['msg']->startsWith('!update') && $user) {
            $this->handleUpdate($user);
        } elseif ($data['msg']->startsWith('!unregister') && $user) {
            $this->handleUnregister($user, $params);
        } elseif ($data['msg']->startsWith('!admin') && $user) {
            $this->handleAdmin($user, $params);
        }
    }
}
