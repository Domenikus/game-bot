<?php

namespace App\Commands;

use App\Assignment;
use App\Game;
use App\Type;
use Closure;
use Exception;
use LaravelZero\Framework\Commands\Command;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\Exception\InvalidTerminalException;

/**
 * @method menu(string $string)
 */
class Menu extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'menu';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Show menu';

    /**
     * Execute the console command.
     *
     * @return void
     *
     * @throws InvalidTerminalException
     */
    public function handle(): void
    {
        $this->buildMenu();
    }

    /**
     * @return void
     *
     * @throws InvalidTerminalException
     */
    private function buildMenu()
    {
        $this->menu('Game bot menu')
            ->addItem('Create Assignment', $this->buildCreateAssignment())
            ->addSubMenu('Delete assignment', $this->buildDeleteAssignment())
            ->addItem('Refresh', function (CliMenu $menu) {
                $menu->close();
                $this->buildMenu();
            })
            ->open();
    }

    private function buildDeleteAssignment(): Closure
    {
        return function (CliMenuBuilder $b) {
            $b->setTitle('Select assignment to delete');
            $assignments = Assignment::with('game')->get();
            foreach ($assignments as $assignment) {
                if ($assignment->game) {
                    $b->addItem($assignment->value.'('.$assignment->game->name.')',
                        function (CliMenu $menu) use ($assignment) {
                            if ($assignment->delete()) {
                                $flash = $menu->flash('Assignment successfully deleted');
                                $flash->getStyle()->setBg('green');
                                $menu->removeItem($menu->getSelectedItem());
                                $menu->redraw();
                            } else {
                                $flash = $menu->flash('Error while deleting assignment');
                                $flash->getStyle()->setBg('red');
                            }

                            $flash->display();
                        });
                }
            }
        };
    }

    private function buildCreateAssignment(): Closure
    {
        return function (CliMenu $menu) {
            /** @phpstan-ignore-next-line */
            $value = $menu
                ->askText()
                ->setPromptText('Enter value')
                ->setValidationFailedText('Invalid value, please provide at least one character')
                ->setValidator(function ($value) {
                    return ! empty($value);
                })
                ->ask();

            $types = Type::all();
            $typesArray = $types->pluck('name')->toArray();

            /** @phpstan-ignore-next-line */
            $type = $menu
                ->askText()
                ->setPromptText('Enter type')
                ->setValidationFailedText('Wrong type, please provide on of the following: '.implode(', ',
                    $typesArray))
                ->setValidator(function ($type) use ($typesArray) {
                    if (in_array($type, $typesArray)) {
                        return true;
                    }

                    return false;
                })->ask();

            $games = Game::all();
            $gamesArray = $games->pluck('name')->toArray();

            /** @phpstan-ignore-next-line */
            $game = $menu->askText()
                ->setPromptText('Enter game')
                ->setValidationFailedText('Wrong game, please provide on of the following: '.implode(', ',
                    $gamesArray))
                ->setValidator(function ($game) use ($gamesArray) {
                    if (in_array($game, $gamesArray)) {
                        return true;
                    }

                    return false;
                })->ask();

            $ts3_server_group_id = $menu->askNumber()
                ->setPromptText('Enter teamspeak server group id')
                ->ask();

            $gameModel = Game::where('name', $game->fetch())->firstOrFail();
            $typeModel = Type::where('name', $type->fetch())->firstOrFail();

            $assignment = new Assignment();
            $assignment->value = $value->fetch();
            $assignment->type()->associate($typeModel);
            $assignment->game()->associate($gameModel);
            $assignment->ts3_server_group_id = $ts3_server_group_id->fetch();

            try {
                $assignment->saveOrFail();
                $flash = $menu->flash('Assignment successfully created');
                $flash->getStyle()->setBg('green');
            } catch (Exception) {
                $flash = $menu->flash('Error while creating assignment');
                $flash->getStyle()->setBg('red');
            }

            $flash->display();
        };
    }
}
