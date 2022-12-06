<?php

namespace App\Services\Listeners;

use App\Services\Gateways\TeamspeakGateway;
use App\Services\UserServiceInterface;
use Illuminate\Support\Facades\Log;
use TeamSpeak3_Adapter_ServerQuery;
use TeamSpeak3_Helper_Signal;

class TimeoutListener implements TeamspeakListener
{
    protected int $autoUpdateInterval;

    protected int $lastUpdate;

    protected UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService, int $autoUpdateInterval)
    {
        $this->autoUpdateInterval = $autoUpdateInterval;
        $this->userService = $userService;
        $this->lastUpdate = time();
    }

    public function init(): void
    {
        TeamSpeak3_Helper_Signal::getInstance()->subscribe('serverqueryWaitTimeout',
            function ($seconds, TeamSpeak3_Adapter_ServerQuery $adapter) {
                if ($adapter->getQueryLastTimestamp() < time() - 180) {
                    Log::info('No reply from the server for '.$seconds.' seconds. Sending keep alive command.');
                    TeamspeakGateway::refreshConnection();
                }

                $this->handleAutoUpdate();
            });
    }

    protected function handleAutoUpdate(): void
    {
        if ($this->lastUpdate < time() - $this->autoUpdateInterval) {
            Log::info('Auto update of server groups started');
            $this->lastUpdate = time();
            $this->userService->handleUpdateAll();
        }
    }
}
