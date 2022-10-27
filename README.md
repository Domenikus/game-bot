# Game bot

Teamspeak bot which syncs stats from games with teamspeak server groups

## Supported games

- League of Legends
  - Most recent played champion
  - Solo/Duo rank
  - Flex rank
  - Prefered lane
- Team fight Tactics
  - Solo rank
  - Double up
- Apex Legends
  - Rank
  - Arena rank
  - Most played legend

## Setup

### Development

Copy env.example file to .env file and fill necessary values

```
composer install
```

```
php game-bot migrate
```

### Production

```
php game-bot app:build
```

## Run

### Development

```
php game-bot run
```

```
php game-bot menu
```

### Production

- Create a query user on the teamspeak server
- Whitelist the ipaddress of the bot or turn of anti-flood-protection
- Get Api keys
    - Apex: [Tracker.gg](https://tracker.gg/developers) (optional)
    - LoL: [Riot developers](https://developer.riotgames.com/apis) (optional)
    - TFT: [Riot developers](https://developer.riotgames.com/apis) (optional)
- Create a bot admin by copying ts3 client identity to admins env variable (optional)
- Run bot menu to map game stats with ts3 server groups
- Setup docker-compose file

```
game-bot:
    container_name: game-bot
    build:
      context: ./game-bot
      dockerfile: Dockerfile
    restart: unless-stopped
    environment:
            TEAMSPEAK_IP: ""
            TEAMSPEAK_PORT: ""
            TEAMSPEAK_QUERY_USER: ""
            TEAMSPEAK_QUERY_PASSWORD: ""
            TEAMSPEAK_QUERY_PORT: ""
            DB_CONNECTION: ""
            DB_HOST: ""
            DB_DATABASE: ""
            DB_USERNAME: ""
            DB_PASSWORD: ""
            APEX_API_KEY: ""
            LOL_API_KEY: ""
            TFT_API_KEY: ""
            AUTO_UPDATE_INTERVAL: "1800"
            ADMINS: ""
```

## Usage

#### User commands

Interacting with the bot from the teamspeak server using the chat

###### Register a game for the current user

```
!register|{game}|{name}|{?platform}
```

###### Unregister specific game

```
!unregister|{game}
```

###### Unregister from all games

```
!unregister
```

###### Update stats manually

```
!update
```

##### Admin commands

###### Unregister given user

```
!admin|unregister|{user}
```

###### Block given user

```
!admin|block|{user}
```

###### Unblock given user

```
!admin|unblock|{user}
```

###### Update all current active clients

```
!admin|update
```

## Quality tools

### PHPStan (Code quality) via [Larastan](https://github.com/nunomaduro/larastan)

This command is used for analyzing your code quality.

`composer analyse`

For IDE integration refer [here](https://www.jetbrains.com/help/phpstorm/using-phpstan.html).

### PHP CS Fixer (Code style) via [Pint](https://laravel.com/docs/9.x/pint)

This command is used to show code style errors.

`composer sniff`

This command will try to auto fix your code.

`composer lint`

For IDE integration refer [here](https://gilbitron.me/blog/running-laravel-pint-in-phpstorm/).

## Configure

### Assignments

What is an assignment? Assignments are the mapping between a game stat and a ts3 server group.
Which means, if you want that players who reach "GOLD I" league in League of Legends, have the server group "Gold", you
have to provide "gold ii" as a value and the id of the server group "gold".
You can do this while using the provided cli menu.

## Contribute

Feel free to extend the functionality or add additional games

