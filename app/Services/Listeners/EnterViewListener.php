<?php

namespace App\Services\Listeners;

use App\Facades\TeamSpeak3;
use App\Services\Gateways\TeamspeakGateway;
use App\Services\UserServiceInterface;
use App\User;
use TeamSpeak3_Adapter_ServerQuery_Event;
use TeamSpeak3_Helper_Signal;
use TeamSpeak3_Node_Host;

class EnterViewListener implements TeamspeakListener
{
    protected UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function init(): void
    {
        TeamSpeak3::notifyRegister('server');

        TeamSpeak3_Helper_Signal::getInstance()->subscribe('notifyCliententerview',
            function (TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host) {
                $data = $event->getData();
                $identityId = $data['client_unique_identifier']->toString();
                $user = User::where('identity_id', $identityId)->first();
                if ($user) {
                    TeamspeakGateway::clearClientCache();
                    $this->userService->handleUpdate($user);
                }
            });
    }
}
