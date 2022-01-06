<?php

namespace App\Commands;

use App\Controllers\RunController;
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


    /**
     * Execute the console command.
     *
     * @param RunController $controller
     * @return void
     */
    public function handle(RunController $controller)
    {
        $this->task("Connect to teamspeak server", function () use (&$controller) {
            $this->newLine();
            $controller->connectToTeamspeakServer();
        });

        $this->task("Initialize event listeners", function () use (&$controller) {
            $this->newLine();
            $controller->setCallback((function (string $message, string $type = RunController::LOG_TYPE_INFO) {
                if ($type == RunController::LOG_TYPE_INFO) {
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
