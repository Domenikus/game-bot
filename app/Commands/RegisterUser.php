<?php

namespace App\Commands;

use App\Services\TeampeakService;
use App\threads\AsyncRegisterListener;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use TeamSpeak3_Transport_Exception;


class RegisterUser extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'register-user';

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
        $server = null;

        $result = $this->task("Connect to teamspeak server", function () use ($service, &$server) {
            $this->newLine();
            try {
                $server = $service->connect();
            } catch (Exception $e) {
                $this->error($e->getMessage());
                return false;
            }

            return true;
        });

        if (!$result) {
            die();
        }

        $this->task("Subscribe for registrations", function () use ($service, $server) {
            $this->newLine();

            $service->subscribeForRegistrations($server, function ($msg) {
                $this->info($msg);
            });
        });

        $this->task("Running bot", function () use (&$server, $service) {
            $this->newLine();

            try {
                while (true) {
                    $server->getAdapter()->wait();
                }
            } catch (TeamSpeak3_Transport_Exception $e) {
                $this->warn(now() . ' Connection error, try to reconnect...');
                sleep(1);
                $this->handleRegistrations($service);
            }
        });
    }

    /**
     * Define the command's schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
    }
}
