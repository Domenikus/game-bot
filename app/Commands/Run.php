<?php

namespace App\Commands;

use App\Services\TeampeakService;
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
    protected $description = 'Run the bot';

    const LOG_TYPE_ERROR = 'error';
    const LOG_TYPE_INFO = 'info';

    /**
     * Execute the console command.
     *
     * @param TeampeakService $service
     * @return void
     */
    public function handle(TeampeakService $service)
    {
        $this->task("Connect to teamspeak server", function () use (&$service) {
            $this->newLine();
            $service->connect();
        });

        $this->task("Initialize event listeners", function () use (&$service) {
            $this->newLine();
            $service->init(function (string $message, string $type = self::LOG_TYPE_INFO) {
                if ($type == self::LOG_TYPE_INFO) {
                    $this->info($message);
                } else {
                    $this->error($message);
                }
            });
        });

        $this->task("Listen for events", function () use (&$service) {
            $this->newLine();
            $service->listen();
        });
    }
}
