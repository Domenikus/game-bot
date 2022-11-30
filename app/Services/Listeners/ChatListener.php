<?php

namespace App\Services\Listeners;

use App\Facades\TeamSpeak3;
use App\Services\UserServiceInterface;
use App\User;
use TeamSpeak3_Adapter_ServerQuery_Event;
use TeamSpeak3_Helper_Signal;
use TeamSpeak3_Node_Host;

class ChatListener implements TeamspeakListener
{
    protected UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function init(): void
    {
        TeamSpeak3::notifyRegister('textserver');
        TeamSpeak3::notifyRegister('textprivate');

        TeamSpeak3_Helper_Signal::getInstance()->subscribe('notifyTextmessage',
            function (TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host) {
                $data = $event->getData();
                $identityId = $data['invokeruid']->toString();
                $user = User::where('identity_id', $identityId)->first();

                $params = explode('|', $data['msg']->toString());
                if ($data['msg']->startsWith('!register')) {
                    $this->userService->handleRegister($identityId, $params);
                } elseif ($data['msg']->startsWith('!update') && $user) {
                    $host->getAdapter()->request('clientupdate');
                    $this->userService->handleUpdate($user);
                } elseif ($data['msg']->startsWith('!unregister') && $user) {
                    $this->userService->handleUnregister($user, $params);
                } elseif ($data['msg']->startsWith('!admin') && $user) {
                    $host->getAdapter()->request('clientupdate');
                    $this->userService->handleAdmin($user, $params);
                } elseif ($data['msg']->startsWith('!help')) {
                    $this->userService->handleHelp($identityId);
                }
            });
    }
}
