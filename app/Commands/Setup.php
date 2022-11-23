<?php

namespace App\Commands;

use App\Game;
use App\Services\GameServiceInterface;
use App\Services\Gateways\GameGatewayRegistry;
use App\Services\Gateways\TeamspeakGateway;
use App\Type;
use LaravelZero\Framework\Commands\Command;

class Setup extends Command
{
    protected $signature = 'setup';

    protected $description = 'Setup games';

    protected GameGatewayRegistry $gatewayRegistry;

    public function handle(GameGatewayRegistry $gatewayRegistry): void
    {
        $this->newLine();
        $this->info('Setup will create Ts3 server groups for all types of stats the bot can synchronize for the given game. Server groups will then assigned to values from the game api\'s');
        $this->info('Server groups will only be created, if there isn\'t already one with the same name. Can also be used to add new server for recently added values like for example new champions in League of Legends');
        if (! $this->confirm('Do you wish to continue?', true)) {
            return;
        }

        $this->gatewayRegistry = $gatewayRegistry;
        $availableGames = Game::withInactive()->with('types')->get();

        /** @var string $selectedGame */
        $selectedGame = $this->choice(
            'Select game',
            $availableGames->pluck('label')->toArray(),
        );

        /** @var Game $selectedGameModel */
        $selectedGameModel = $availableGames->where('label', $selectedGame)->first();
        $this->setupGame($selectedGameModel);
    }

    protected function setupGame(Game $game): void
    {
        $permissions = $this->askForServerGroupPermissions();
        $types = $game->types;
        $progressBar = $this->output->createProgressBar();
        $gameService = $this->app->make(GameServiceInterface::class, ['game' => $game]);
        if ($gameService instanceof GameServiceInterface) {
            foreach ($types as $type) {
                $this->task('Setup '.$type->game_type->label, function () use ($game, $gameService, $progressBar, $permissions, $type) {
                    $typeSuffix = $this->askForServerGroupSuffix($type, $game);

                    return $gameService->setup($type, $permissions, $progressBar, $typeSuffix);
                });
            }
        }
    }

    /**
     * @return array<?array{id: int, value: int}>
     */
    protected function askForServerGroupPermissions(): array
    {
        $permissions = [];
        $availablePermissions = TeamspeakGateway::getAvailablePermissions();
        if (! empty($availablePermissions)) {
            while ($this->confirm('Do you want to assign permissions to the Teamspeak server groups which will be created?')) {
                $permissionName = $this->anticipate('Which permission do you want to add?', array_keys($availablePermissions));
                $permissionValue = $this->ask('Which value should the permission have?');

                if (isset($availablePermissions[$permissionName]) && is_numeric($permissionValue)) {
                    $permissions[] = [
                        'id' => $availablePermissions[$permissionName],
                        'value' => (int) $permissionValue,
                    ];
                } else {
                    $this->error('Invalid permission. Permission will not be added to the list. Please try again');
                }
            }
        }

        return $permissions;
    }

    protected function askForServerGroupSuffix(Type $type, Game $game): string
    {
        $suffix = '';
        $this->newLine();
        if ($this->confirm('Do you want to change default naming of server groups for Type: <options=bold>'.$type->game_type->label.'.</>? Template: {Value} (<Game>-<type>). Example: Aatrox (LoL-Champion). <fg=yellow>If you want to remove the suffix completely, enter 0!</>')) {
            $answer = $this->ask('Which suffix do you want to add?');

            if (! is_numeric($answer) && $answer != 0) {
                $suffix = ' ('.$answer.')';
            }
        } else {
            $suffix = ' ('.ucfirst($game->name).'-'.$type->game_type->label.')';
        }

        return $suffix;
    }
}
