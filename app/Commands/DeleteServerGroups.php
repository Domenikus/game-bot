<?php

namespace App\Commands;

use App\Services\Gateways\TeamspeakGateway;
use Illuminate\Support\Facades\Log;
use LaravelZero\Framework\Commands\Command;
use TeamSpeak3_Node_Servergroup;

class DeleteServerGroups extends Command
{
    protected $description = 'Deletes all server groups greater or equal the given server group id offset';

    protected $signature = 'ts3:delete-server-groups {offset : Server group id offset }';

    public function handle(): void
    {
        if (! $this->confirm('Are you sure what you are going to do?')) {
            return;
        }

        $serverGroupOffset = $this->argument('offset');
        if (! is_numeric($serverGroupOffset)) {
            Log::error('Non numeric offset given');

            return;
        }

        $this->info('Delete Teamspeak server groups');
        $this->withProgressBar(TeamspeakGateway::getServerGroups(), function (TeamSpeak3_Node_Servergroup $serverGroup) use ($serverGroupOffset) {
            if ($serverGroup->getId() >= (int) $serverGroupOffset) {
                $serverGroup->delete(true);
            }
        });
    }
}
