<?php

namespace App\Commands;

use App\Facades\TeamSpeak3;
use App\Listeners\TeamspeakListenerRegistry;
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

    public function handle(TeamspeakListenerRegistry $listenerRegistry): void
    {
        $this->task('Connect to teamspeak server', function () {
            $this->newLine();
        });

        $this->task('Initialize event listeners', function () use (&$listenerRegistry) {
            $this->newLine();

            foreach ($listenerRegistry->getAll() as $listener) {
                $listener->init();
            }
        });

        $this->task('Listen for events', function () {
            $this->newLine();
            $this->listenToEvents();
        });
    }

    public function listenToEvents(): void
    {
        /** @phpstan-ignore-next-line */ // Intended behavior, application loop
        while (1) {
            TeamSpeak3::getAdapter()->wait();
        }
    }
}
