<?php

namespace App\Services\Listeners;

use App\Services\UserServiceInterface;
use Illuminate\Support\Facades\Log;
use TeamSpeak3_Adapter_ServerQuery;
use TeamSpeak3_Helper_Signal;

class TimeoutListener implements TeamspeakListener
{
    protected ?int $lastUpdate = null;

    protected int $autoUpdateInterval;

    protected UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService, int $autoUpdateInterval)
    {
        $this->autoUpdateInterval = $autoUpdateInterval;
        $this->userService = $userService;
    }

    public function init(): void
    {
        TeamSpeak3_Helper_Signal::getInstance()->subscribe('serverqueryWaitTimeout',
            function ($seconds, TeamSpeak3_Adapter_ServerQuery $adapter) {
                if ($adapter->getQueryLastTimestamp() < time() - 180) {
                    Log::info('No reply from the server for '.$seconds.' seconds. Sending keep alive command.');
                    $adapter->request('clientupdate');
                }

                $this->handleAutoUpdate();
            });
    }

    protected function handleAutoUpdate(): void
    {
        if (! $this->lastUpdate || $this->lastUpdate < time() - $this->autoUpdateInterval) {
            $this->lastUpdate = time();
            $this->userService->handleUpdateAll();
        }
    }
}
