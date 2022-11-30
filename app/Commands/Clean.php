<?php

namespace App\Commands;

use App\Assignment;
use App\Game;
use App\GameUser;
use App\Services\Gateways\TeamspeakGateway;
use LaravelZero\Framework\Commands\Command;

class Clean extends Command
{
    protected $description = 'Removes all teamspeak server groups the bot has assignments for and all registered users';

    protected $signature = 'clean';

    public function handle(): void
    {
        if (! $this->confirm('Are you sure what you are going to do?')) {
            return;
        }

        $this->info('Delete Teamspeak server groups');
        $this->withProgressBar(Assignment::all(), function (Assignment $assignment) {
            TeamspeakGateway::deleteServerGroup($assignment->ts3_server_group_id);
            $assignment->delete();
        });

        $this->info('Set games to inactive');
        $this->withProgressBar(Game::all(), function (Game $game) {
            $game->active = false;
            $game->save();
        });

        $this->info('Delete registered users');
        $this->withProgressBar(GameUser::all(), function (GameUser $gameUser) {
            $gameUser->delete();
        });
    }
}
