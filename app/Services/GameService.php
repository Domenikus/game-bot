<?php

namespace App\Services;

use App\Assignment;
use App\Game;
use App\Services\Gateways\GameGateway;
use App\Services\Gateways\GameGatewayFactoryInterface;
use App\Services\Gateways\TeamspeakGateway;
use App\Type;
use Illuminate\Support\Facades\App;
use Symfony\Component\Console\Helper\ProgressBar;

class GameService implements GameServiceInterface
{
    protected Game $game;

    protected GameGateway $gameGateway;

    public function __construct(Game $game)
    {
        $this->game = $game;
        $gameGatewayFactory = App::make(GameGatewayFactoryInterface::class);
        $this->gameGateway = $gameGatewayFactory->create($game->name);
    }

    public function grabImage(Type $type, string $value): ?string
    {
        $result = null;

        switch ($type->name) {
            case Type::NAME_CHARACTER:
                $result = $this->gameGateway->grabCharacterImage($value);
                break;
            case Type::NAME_POSITION:
                $result = $this->gameGateway->grabPositionImage($value);
                break;
            case Type::NAME_RANK_SOLO:
            case Type::NAME_RANK_DUO:
            case Type::NAME_RANK_GROUP:
                $result = $this->gameGateway->grabRankImage($value);
                break;
        }

        return $result;
    }

    public function grabValues(Type $type): ?array
    {
        $values = null;

        switch ($type->name) {
            case Type::NAME_CHARACTER:
                $values = $this->gameGateway->grabCharacters();
                break;
            case Type::NAME_POSITION:
                $values = $this->gameGateway->grabPositions();
                break;
            case Type::NAME_RANK_SOLO:
            case Type::NAME_RANK_DUO:
            case Type::NAME_RANK_GROUP:
                $values = $this->gameGateway->grabRanks();
                break;
        }

        return $values;
    }

    public function setup(Type $type, array $permissions, int $sortIndex, ProgressBar $progressBar = null, string $suffix = null): bool
    {
        $values = $this->grabValues($type);
        if (! $values) {
            return false;
        }

        if ($progressBar) {
            $progressBar->setMaxSteps(count($values));
            $progressBar->start();
        }

        foreach ($values as $value) {
            $progressBar?->advance();

            if (Assignment::where('value', $value)
                ->where('game_id', $this->game->id)
                ->where('type_id', $type->id)->first()) {
                continue;
            }

            if (! $serverGroup = TeamspeakGateway::getServerGroupByName($value.$suffix)) {
                $serverGroupId = TeamspeakGateway::createServerGroup($value.$suffix);
                if (! $serverGroupId) {
                    return false;
                }

                $imageData = $this->grabImage($type, $value);
                if ($imageData) {
                    $calculatedIconId = TeamspeakGateway::calculateIconId($imageData);
                    $iconId = TeamspeakGateway::iconExists($calculatedIconId) ? $calculatedIconId : TeamspeakGateway::uploadIcon($imageData);
                    if ($iconId) {
                        TeamspeakGateway::assignServerGroupIcon($serverGroupId, $iconId);
                    }
                }

                TeamspeakGateway::assignServerGroupSortId($serverGroupId, $sortIndex);
                $sortIndex++;

                foreach ($permissions as $permission) {
                    TeamspeakGateway::assignServerGroupPermission($serverGroupId, $permission['id'],
                        $permission['value']);
                }
            } else {
                $serverGroupId = $serverGroup->getId();
            }

            $assignment = new Assignment();
            $assignment->value = $value;
            $assignment->ts3_server_group_id = $serverGroupId;
            $assignment->type()->associate($type);
            $assignment->game()->associate($this->game);
            $assignment->save();
        }

        $progressBar?->finish();

        return true;
    }
}
