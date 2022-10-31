<?php

namespace App\Listeners;

use App\Facades\TeamSpeak3;
use App\User;
use TeamSpeak3_Adapter_ServerQuery_Event;
use TeamSpeak3_Helper_Signal;
use TeamSpeak3_Node_Host;

class EnterViewListener extends AbstractListener
{
    public function init(): void
    {
        TeamSpeak3::notifyRegister('server');

        TeamSpeak3_Helper_Signal::getInstance()->subscribe('notifyCliententerview',
            function (TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host) {
                $data = $event->getData();
                $identityId = $data['client_unique_identifier']->toString();
                $user = User::where('identity_id', $identityId)->first();
                if ($user) {
                    $this->handleUpdate($user);
                }
            });
    }
}
