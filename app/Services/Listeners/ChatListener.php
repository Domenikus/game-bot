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

    protected string $chatCommandPrefix;

    public function __construct(UserServiceInterface $userService, string $chatCommandPrefix)
    {
        $this->userService = $userService;
        $this->chatCommandPrefix = $chatCommandPrefix;
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

                if (mb_substr($params[0], 0, 1) == $this->chatCommandPrefix) {
                    if (mb_strpos($params[0], 'register')) {
                        $this->userService->handleRegister($identityId, $params);
                    } elseif (mb_strpos($params[0], 'update') && $user) {
                        $host->getAdapter()->request('clientupdate');
                        $this->userService->handleUpdate($user);
                    } elseif (mb_strpos($params[0], 'unregister') && $user) {
                        $this->userService->handleUnregister($user, $params);
                    } elseif (mb_strpos($params[0], 'admin') && $user) {
                        $host->getAdapter()->request('clientupdate');
                        $this->userService->handleAdmin($user, $params);
                    } elseif (mb_strpos($params[0], 'help')) {
                        $this->userService->handleHelp($identityId);
                    }
                }
            });
    }
}
