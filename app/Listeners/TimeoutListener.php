<?php


namespace App\Listeners;


use App\Commands\Run;
use TeamSpeak3_Adapter_ServerQuery;
use TeamSpeak3_Helper_Signal;

class TimeoutListener extends AbstractListener
{
    function init()
    {
        TeamSpeak3_Helper_Signal::getInstance()->subscribe("serverqueryWaitTimeout", function ($seconds, TeamSpeak3_Adapter_ServerQuery $adapter) {
            if ($adapter->getQueryLastTimestamp() < time() - 180) {
                call_user_func($this->callback, "No reply from the server for " . $seconds . " seconds. Sending keep alive command.", Run::LOG_TYPE_INFO);
                $adapter->request("clientupdate");
                $this->server = $adapter->getHost()->serverGetSelected();
            }
        });
    }
}
