<?php

namespace App\Commands;

use App\Facades\TeamSpeak3;
use App\Listeners\EnterViewListener;
use App\Listeners\GlobalChatListener;
use App\Listeners\PrivateChatListener;
use App\Listeners\TimeoutListener;
use LaravelZero\Framework\Commands\Command;

class Run extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'run';

    /**
     * @var string
     */
    protected $description = 'Run the bot';

    public function handle(): void
    {
        $this->task('Connect to teamspeak server', function () {
            $this->newLine();
        });

        $this->task('Initialize event listeners', function () {
            $this->newLine();
            $this->initListener();
        });

        $this->task('Listen for events', function () {
            $this->newLine();
            $this->listenToEvents();
        });
    }

    private function initListener(): void
    {
        $listeners = [];

        if (config('teamspeak.listener.globalChat')) {
            $listeners[] = new GlobalChatListener();
        }

        if (config('teamspeak.listener.privateChat')) {
            $listeners[] = new PrivateChatListener();
        }

        if (config('teamspeak.listener.enterView')) {
            $listeners[] = new EnterViewListener();
        }

        $listeners[] = new TimeoutListener();

        foreach ($listeners as $listener) {
            $listener->init();
        }
    }

    public function listenToEvents(): void
    {
        /** @phpstan-ignore-next-line */ // Intended behavior, application loop
        while (1) {
            TeamSpeak3::getAdapter()->wait();
        }
    }
}
