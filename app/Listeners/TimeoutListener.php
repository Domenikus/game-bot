<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use TeamSpeak3_Adapter_ServerQuery;
use TeamSpeak3_Adapter_ServerQuery_Exception;
use TeamSpeak3_Helper_Signal;

class TimeoutListener extends AbstractListener
{
    protected ?int $lastUpdate = null;

    public function init(): void
    {
        TeamSpeak3_Helper_Signal::getInstance()->subscribe('serverqueryWaitTimeout',
            function ($seconds, TeamSpeak3_Adapter_ServerQuery $adapter) {
                if ($adapter->getQueryLastTimestamp() < time() - 180) {
                    Log::info('No reply from the server for " . $seconds . " seconds. Sending keep alive command.');
                    $adapter->request('clientupdate');
                    $this->server = $adapter->getHost()->serverGetSelected();
                }

                $this->handleAutoUpdate();
            });
    }

    /**
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    protected function handleAutoUpdate(): void
    {
        if (config('teamspeak.auto_update_interval')) {
            if (! $this->lastUpdate || $this->lastUpdate < time() - config('teamspeak.auto_update_interval')) {
                $this->lastUpdate = time();
                $this->updateActiveClients();
            }
        }
    }
}
