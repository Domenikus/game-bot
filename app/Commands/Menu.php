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
     * @throws InvalidTerminalException
     */
    public function handle()
    {
        $this->buildMenu();
    }

    /**
     * @return void
     * @throws InvalidTerminalException
     */
    private function buildMenu()
    {
        $this->menu('Game bot menu')
            ->addItem('Create Assignment', $this->buildCreateAssignment())
            ->addSubMenu('Delete assignment', $this->buildDeleteAssignment())
            ->addItem('Create game', $this->buildCreateGame())
            ->addSubMenu('Delete game', $this->buildDeleteGame())
            ->addItem('Create type', $this->buildCreateType())
            ->addSubMenu('Delete type', $this->buildDeleteType())
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
            $assignments = Assignment::all();
            foreach ($assignments as $assignment) {
                $b->addItem($assignment->value . '(' . $assignment->game->name . ')', function (CliMenu $menu) use ($assignment) {
                    if ($assignment->delete()) {
                        $flash = $menu->flash("Assignment successfully deleted");
                        $flash->getStyle()->setBg('green');
                        $menu->removeItem($menu->getSelectedItem());
                        $menu->redraw();
                    } else {
                        $flash = $menu->flash("Error while deleting assignment");
                        $flash->getStyle()->setBg('red');
                    }

                    $flash->display();
                });
            }
        };
    }

    private function buildCreateAssignment(): Closure
    {
        return function (CliMenu $menu) {
            $value = $menu->askText()
                ->setPromptText("Enter value")
                ->setPlaceholderText("Some value")
                ->ask();

            $typesArray = [];
            if ($types = Type::all()) {
                $typesArray = $types->pluck('name')->toArray();
            }

            $type = $menu->askText()
                ->setPromptText('Enter type')
                ->setPlaceholderText('Some type')
                ->setValidationFailedText('Wrong type, please provide on of the following: ' . implode(', ', $typesArray))
                ->setValidator(function ($type) use ($typesArray) {
                    if (in_array($type, $typesArray)) {
                        return true;
                    }

                    return false;
                })->ask();

            $gamesArray = [];
            if ($games = Game::all()) {
                $gamesArray = $games->pluck('name')->toArray();
            }

            $game = $menu->askText()
                ->setPromptText('Enter game')
                ->setPlaceholderText('Some game')
                ->setValidationFailedText('Wrong game, please provide on of the following: ' . implode(', ', $gamesArray))
                ->setValidator(function ($game) use ($gamesArray) {
                    if (in_array($game, $gamesArray)) {
                        return true;
                    }

                    return false;
                })->ask();

            $ts3_server_group_id = $menu->askNumber()
                ->setPromptText("Enter teamspeak server group id")
                ->setPlaceholderText("Some id")
                ->ask();

            $gameModel = Game::where('name', $game->fetch())->firstOrFail();
            $typeModel = Type::where('name', $type->fetch())->firstOrFail();

            $assignment = new Assignment();
            $assignment->value = strtolower($value->fetch());
            $assignment->type()->associate($typeModel);
            $assignment->game()->associate($gameModel);
            $assignment->ts3_server_group_id = $ts3_server_group_id->fetch();

            try {
                $assignment->saveOrFail();
                $flash = $menu->flash("Assignment successfully created");
                $flash->getStyle()->setBg('green');
            } catch (Exception) {
                $flash = $menu->flash("Error while creating assignment");
                $flash->getStyle()->setBg('red');
            }

            $flash->display();
        };
    }

    private function buildCreateGame(): Closure
    {
        return function (CliMenu $menu) {
            $name = $menu->askText()
                ->setPromptText('Enter game name')
                ->setPlaceholderText('Some name')
                ->setValidationFailedText('Game name is already chosen')
                ->setValidator(function ($name) {
                    if (Game::where('name', $name)->first()) {
                        return false;
                    }

                    return true;
                })->ask();

            $gameModel = new Game();
            $gameModel->name = $name->fetch();

            try {
                $gameModel->saveOrFail();
                $flash = $menu->flash("Game successfully created");
                $flash->getStyle()->setBg('green');
            } catch (Exception) {
                $flash = $menu->flash("Error while creating game");
                $flash->getStyle()->setBg('red');
            }

            $flash->display();
        };
    }

    private function buildDeleteGame(): Closure
    {
        return function (CliMenuBuilder $b) {
            $b->setTitle('Select game to delete');
            $games = Game::all();
            foreach ($games as $game) {
                $b->addItem($game->value . '(' . $game->name . ')', function (CliMenu $menu) use ($game) {
                    if ($game->delete()) {
                        $flash = $menu->flash("Game successfully deleted");
                        $flash->getStyle()->setBg('green');
                        $menu->removeItem($menu->getSelectedItem());
                        $menu->redraw();
                    } else {
                        $flash = $menu->flash("Error while deleting game");
                        $flash->getStyle()->setBg('red');
                    }

                    $flash->display();
                });
            }
        };
    }

    private function buildCreateType(): Closure
    {
        return function (CliMenu $menu) {
            $name = $menu->askText()
                ->setPromptText('Enter type name')
                ->setPlaceholderText('Some name')
                ->setValidationFailedText('Type name is already chosen')
                ->setValidator(function ($name) {
                    if (Type::where('name', $name)->first()) {
                        return false;
                    }

                    return true;
                })->ask();

            $typeModel = new Type();
            $typeModel->name = $name->fetch();

            try {
                $typeModel->saveOrFail();
                $flash = $menu->flash("Type successfully created");
                $flash->getStyle()->setBg('green');
            } catch (Exception) {
                $flash = $menu->flash("Error while creating type");
                $flash->getStyle()->setBg('red');
            }

            $flash->display();
        };
    }

    private function buildDeleteType(): Closure
    {
        return function (CliMenuBuilder $b) {
            $b->setTitle('Select type to delete');
            $types = Type::all();
            foreach ($types as $type) {
                $b->addItem($type->value . '(' . $type->name . ')', function (CliMenu $menu) use ($type) {
                    if ($type->delete()) {
                        $flash = $menu->flash("Type successfully deleted");
                        $flash->getStyle()->setBg('green');
                        $menu->removeItem($menu->getSelectedItem());
                        $menu->redraw();
                    } else {
                        $flash = $menu->flash("Error while deleting type");
                        $flash->getStyle()->setBg('red');
                    }

                    $flash->display();
                });
            }
        };
    }
}
