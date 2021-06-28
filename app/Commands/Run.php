<?php

namespace App\Commands;

use App\Controllers\AppController;
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
     * @param AppController $controller
     * @return void
     */
    public function handle(AppController $controller)
    {
        $this->task("Connect to teamspeak server", function () use (&$controller) {
            $this->newLine();
            $controller->connectToTeamspeakServer();
        });

        $this->task("Initialize event listeners", function () use (&$controller) {
            $this->newLine();
            $controller->setCallback((function (string $message, string $type = self::LOG_TYPE_INFO) {
                if ($type == self::LOG_TYPE_INFO) {
                    $this->info($message);
                } else {
                    $this->error($message);
                }
            }));

            $controller->initListener();
        });

        $this->task("Listen for events", function () use (&$controller) {
            $this->newLine();
            $controller->listenToEvents();
        });
    }
}
