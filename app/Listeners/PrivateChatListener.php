<?php

namespace App\Listeners;

use TeamSpeak3_Adapter_ServerQuery_Event;
use TeamSpeak3_Helper_Signal;
use TeamSpeak3_Node_Host;

class PrivateChatListener extends GlobalChatListener
{
    public function init(): void
    {
        $this->server->notifyRegister('textprivate');

        TeamSpeak3_Helper_Signal::getInstance()->subscribe('notifyTextmessage',
            function (TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host) {
                $this->handle($event, $host);
            });
    }
}
