[b][/b]

[COLOR=#00aaff][B]Bot commands:[/B][/COLOR]

[B]Update all games you are registered for:[/B]
{{$commandPrefix}}update

[B]Register:[/B]
{{$commandPrefix}}register|{game}|{...}

[B]Unregister:[/B]
{{$commandPrefix}}unregister|{?game} Unregister from specific game
{{$commandPrefix}}unregister Unregister from all games

[B]Show and hide game types:[/B]
{{$commandPrefix}}show|{game}|{type}
{{$commandPrefix}}hide|{game}|{type}

[B]Available types:[/B]
@foreach($activeGames as $game)
    - {{$game->label}}
    @foreach($game->types as $type)
        - {{$type->game_type->label}} --> {{$type->name}}
    @endforeach

@endforeach

[B]Show help:[/B]
{{$commandPrefix}}help

[COLOR=#00aaff][B]Examples:[/B][/COLOR]

{{$commandPrefix}}register|apex|Skittlecakes|origin
{{$commandPrefix}}unregister|apex

{{$commandPrefix}}register|lol|Faker
{{$commandPrefix}}unregister|lol

{{$commandPrefix}}register|tft|Faker
{{$commandPrefix}}unregister|tft
