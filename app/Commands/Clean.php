<?php

namespace App\Commands;

use App\Assignment;
use App\Services\Gateways\TeamspeakGateway;
use LaravelZero\Framework\Commands\Command;

class Clean extends Command
{
    protected $description = 'Removes all teamspeak server groups the bot has assignments for.';
    protected $signature = 'ts3:clean';

    public function handle(): void
    {
        if (! $this->confirm('Are you sure what you are going to do?')) {
            return;
        }

        $this->withProgressBar(Assignment::all(), function (Assignment $assignment) {
            TeamspeakGateway::deleteServerGroup($assignment->ts3_server_group_id);
            $assignment->delete();
        });
    }
}
