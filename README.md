# Game bot

Teamspeak bot which syncs stats from games with teamspeak server groups

## Supported games

- League of Legends
    - Most recent played champion
    - Solo/Duo rank
    - Flex rank
    - Preferred lane
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

## Run

### Commands

Run the bot

```
php game-bot run
```

Shows setup menu to set up supported games (recommended)

```
php game-bot setup
```

Revers all changes done by the setup (Removes teamspeak server groups and assignments)

```
php game-bot clean
```

Shows menu to creat and edit assignments manually. (Not recommended)

```
php game-bot menu
```

## Setup for production

- Create a query user on the teamspeak server, see necessary permission down below
- Whitelist the ipaddress of the bot or turn of anti-flood-protection
- Get Api keys
    - Apex Legends: [Tracker.gg](https://tracker.gg/developers) (optional)
    - League of Legends: [Riot developers](https://developer.riotgames.com/apis) (optional)
    - Teamfight Tactics: [Riot developers](https://developer.riotgames.com/apis) (optional)
- Create a bot admin by copying ts3 client identity to admins env variable (optional)
- Run bot menu to map game stats with ts3 server groups
- Setup docker-compose file

### Docker-compose example

```
game-bot:
    container_name: game-bot
    image: domenikus/game-bot
    restart: unless-stopped
    environment:
        TEAMSPEAK_IP=127.0.0.1
        TEAMSPEAK_PORT=9987
        TEAMSPEAK_QUERY_USER=
        TEAMSPEAK_QUERY_PASSWORD=
        TEAMSPEAK_QUERY_PORT=10011
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_DATABASE=
        DB_USERNAME=
        DB_PASSWORD=
        DB_PORT=3307
        APEX_API_KEY=
        LOL_API_KEY=
        LOL_REGION=euw1
        TFT_API_KEY=
        AUTO_UPDATE_INTERVAL=1800
        ADMINS=
        LOG_CHANNEL=stack
        LOG_LEVEL=info
```

### Necessary bot permissions in ts3 server

```
b_serverinstance_permission_list
b_virtualserver_client_list
b_virtualserver_notify_register
b_virtualserver_notify_unregister
b_virtualserver_servergroup_list
b_virtualserver_channelgroup_list
b_virtualserver_servergroup_create
i_group_modify_power
i_group_member_add_power
i_group_needed_member_add_power
b_permission_modify_power_ignore
b_virtualserver_servergroup_delete
i_max_icon_filesize
b_icon_manage
b_group_is_permanent
i_client_permission_modify_power
b_client_ignore_antiflood
```

## Usage

#### User commands

Interacting with the bot from the teamspeak server using the chat

##### Show user commands

`
!help
`

##### Show admin commands

`
!admin|help
`

## Configure

There is no need to configure the application manually, just run the setup and you are good to go. In case you want to
do it manually the following explanation may help you.

### Assignments

What is an assignment? Assignments are the mapping between a game stat and a ts3 server group.
Which means, if you want that players who reach "GOLD I" tier in League of Legends, gets the server group "Gold", you
have to provide "gold i" as a value and the id of the server group "gold".
You can do this while using the provided cli menu.

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

## Contribute

Feel free to extend the functionality or add additional games. Pull request are welcome.

