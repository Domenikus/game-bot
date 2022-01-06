<?php

namespace App\Commands;

use App\Assignment;
use App\Game;
use App\Type;
use Closure;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\Input\InputIO;
use PhpSchool\CliMenu\Input\Text;
use PhpSchool\CliMenu\MenuStyle;


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
     */
    public function handle()
    {
        $this->buildMenu();
    }

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

            $gameModel = Game::where(['name' => $game->fetch()])->firstOrFail();
            $typeModel = Type::where(['name' => $type->fetch()])->firstOrFail();

            $assignment = new Assignment();
            $assignment->value = $value->fetch();
            $assignment->type()->associate($typeModel);
            $assignment->game()->associate($gameModel);
            $assignment->ts3_server_group_id = $ts3_server_group_id->fetch();

            try {
                $assignment->saveOrFail();
                $flash = $menu->flash("Assignment successfully created");
                $flash->getStyle()->setBg('green');
            } catch (\Exception $e) {
                $flash = $menu->flash("Error while creating assignment");
                $flash->getStyle()->setBg('red');
            }

            $flash->display();
        };
    }
}