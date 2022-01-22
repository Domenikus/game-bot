<?php

namespace Tests\Unit;

use App\Game;
use App\Listeners\AbstractListener;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbstractListenerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testHandleUpdate()
    {
        $user = User::factory()->make();
        $lol = Game::where('name', Game::NAME_LEAGUE_OF_LEGENDS)->first();
        $apex = Game::where('name', Game::NAME_APEX)->first();
        $tft = Game::where('name', Game::NAME_TEAMFIGHT_TACTICS)->first();
        $options = ['test' => 'test'];

//        $user->games()->attach($lol->getKey(), ['options' => $options]);
//        $user->games()->attach($lol->getKey(), ['options' => $options]);
//        $user->games()->attach($lol->getKey(), ['options' => $options]);


        $t = $this->getMockBuilder(AbstractListener::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

//        $t->handleUpdate($user);

        $t->expects($this->once())
            ->method('updateServerGroups');

    }
}
