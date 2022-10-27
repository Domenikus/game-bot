<?php

namespace App\Commands;

use App\Listeners\EnterViewListener;
use App\Listeners\GlobalChatListener;
use App\Listeners\PrivateChatListener;
use App\Listeners\TimeoutListener;
use App\Services\Teamspeak;
use LaravelZero\Framework\Commands\Command;
use TeamSpeak3_Node_Server;

class Run extends Command
{
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
    public function handle(): void
    {
        $server = null;

        $isConnectedToTeamspeakServer = $this->task('Connect to teamspeak server', function () use (&$server) {
            $this->newLine();

            if ($teamspeakServer = Teamspeak::connectToTeamspeakServer()) {
                $server = $teamspeakServer;

                return true;
            }

            return false;
        });

        if (! $isConnectedToTeamspeakServer) {
            return;
        }

        $this->task('Initialize event listeners', function () use (&$server) {
            $this->newLine();
            /** @phpstan-ignore-next-line */ // Already checked in line 41
            $this->initListener($server);
        });

        $this->task('Listen for events', function () use (&$server) {
            $this->newLine();
            /** @phpstan-ignore-next-line */ // Already checked in line 41
            $this->listenToEvents($server);
        });
    }

    private function initListener(TeamSpeak3_Node_Server $server): void
    {
        $listeners = [];

        if (config('teamspeak.listener.globalChat')) {
            $listeners[] = new GlobalChatListener($server);
        }

        if (config('teamspeak.listener.privateChat')) {
            $listeners[] = new PrivateChatListener($server);
        }

        if (config('teamspeak.listener.enterView')) {
            $listeners[] = new EnterViewListener($server);
        }

        $listeners[] = new TimeoutListener($server);

        foreach ($listeners as $listener) {
            $listener->init();
        }
    }

    public function listenToEvents(TeamSpeak3_Node_Server $server): void
    {
        /** @phpstan-ignore-next-line */ // Intended behavior
        while (true) {
            /** @phpstan-ignore-next-line */ // Unfortunately no type hint in teamspeak library
            $server->getAdapter()->wait();
        }
    }
}
