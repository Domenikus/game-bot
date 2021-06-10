<?php

namespace App\Commands;

use App\Services\TeampeakService;
use Exception;
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
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @param TeampeakService $service
     * @return void
     */
    public function handle(TeampeakService $service)
    {
        $this->handleRegistrations($service);
    }

    private function handleRegistrations(TeampeakService $service)
    {
        $result = $this->task("Connect to teamspeak server", function () use (&$service) {
            $this->newLine();
            try {
                $service->connect();
            } catch (Exception $e) {
                $this->error($e->getMessage());
                return false;
            }

            return true;
        });

        if (!$result) {
            die();
        }

        $this->task("Initialize event listeners", function () use (&$service) {
            $this->newLine();
            $service->initializeEventListeners(function ($msg) {
                $this->info($msg);
            });
        });

        $this->task("Listen for events", function () use (&$service) {
            $this->newLine();
            $service->listen();
        });
    }
}
