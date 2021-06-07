<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class CheckStats extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'check-stats';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Check stats of all registered users';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Simplicity is the ultimate sophistication.');
    }

    /**
     * Define the command's schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
         $schedule->command(static::class)->everyFiveMinutes();
    }
}
