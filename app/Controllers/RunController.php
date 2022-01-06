<?php


namespace App\Controllers;


use App\Listeners\EnterViewListener;
use App\Listeners\GlobalChatListener;
use App\Listeners\TimeoutListener;
use Exception;
use TeamSpeak3;
use TeamSpeak3_Node_Server;

class RunController
{
    const LOG_TYPE_ERROR = 'error';
    const LOG_TYPE_INFO = 'info';

    /**
     * @var TeamSpeak3_Node_Server
     */
    private $server;

    /**
     * @var callable
     */
    private $callback;

    /**
     * AppController constructor.
     * @param callable|null $callback
     */
    public function __construct(callable $callback = null)
    {
        if (!$callback) {
            $this->callback = function () {
            };
            return;
        }

        $this->callback = $callback;
    }

    /**
     * @param callable|null $callback
     */
    public function setCallback(callable $callback): void
    {
        $this->callback = $callback;
    }

    /**
     *
     * @throws Exception
     */
    public function connectToTeamspeakServer(): void
    {
        $uri = "serverquery://"
            . config('teamspeak.query_user') . ":"
            . config('teamspeak.query_password') . "@"
            . config('teamspeak.ip') . ":"
            . config('teamspeak.query_port') . "/?server_port="
            . config('teamspeak.port') . "&blocking=0";
        $this->server = TeamSpeak3::factory($uri);
    }

    public function initListener()
    {
        $listeners = [];

        if (config('teamspeak.listener.globalChat')) {
            $listeners[] = new GlobalChatListener($this->server, $this->callback);
        }

        if (config('teamspeak.listener.enterView')) {
            $listeners[] = new EnterViewListener($this->server, $this->callback);
        }

        $listeners[] = new TimeoutListener($this->server, $this->callback);

        foreach ($listeners as $listener) {
            $listener->init();
        }
    }

    public function listenToEvents(): void
    {
        while (true) {
            $this->server->getAdapter()->wait();
        }
    }
}
