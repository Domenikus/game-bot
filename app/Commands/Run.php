<?php

namespace App\Commands;

use App\Controllers\RunController;
use App\Interfaces\Teamspeak;
use App\Listeners\EnterViewListener;
use App\Listeners\GlobalChatListener;
use App\Listeners\TimeoutListener;
use LaravelZero\Framework\Commands\Command;
use TeamSpeak3_Node_Server;


class Run extends Command
{
    const LOG_TYPE_INFO = 'info';

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'run';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Run the bot';


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $server = null;
        $callback = function (string $message, string $type = Run::LOG_TYPE_INFO) {
            if ($type == Run::LOG_TYPE_INFO) {
                $this->info($message);
            } else {
                $this->error($message);
            }
        };

        $this->task("Connect to teamspeak server", function () use (&$server) {
            $this->newLine();
            $server = Teamspeak::connectToTeamspeakServer();
        });

        $this->task("Initialize event listeners", function () use (&$server, &$callback) {
            $this->newLine();
            $this->initListener($server, $callback);
        });

        $this->task("Listen for events", function () use (&$server) {
            $this->newLine();
            $this->listenToEvents($server);
        });
    }

    private function initListener(TeamSpeak3_Node_Server $server, callable $callback): void
    {
        $listeners = [];

        if (config('teamspeak.listener.globalChat')) {
            $listeners[] = new GlobalChatListener($server, $callback);
        }

        if (config('teamspeak.listener.enterView')) {
            $listeners[] = new EnterViewListener($server, $callback);
        }

        $listeners[] = new TimeoutListener($server, $callback);

        foreach ($listeners as $listener) {
            $listener->init();
        }
    }

    public function listenToEvents(TeamSpeak3_Node_Server $server): void
    {
        while (true) {
            $server->getAdapter()->wait();
        }
    }
}
