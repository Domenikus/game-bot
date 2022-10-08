# Game bot

Teamspeak bot which syncs Teamspeak server groups with stata from games like LeagueOfLegends, TeamfightTactics or Apex Legends

## Setup:

```
composer install
php game-bot migrate
```

## Run:

### Bot:

```
php game-bot run
```

### Menu:

```
php game-bot menu
```

## Build for production

```
php game-bot app:build
```

## Bot chat commands

### Register

```
!register|{game}|{name}|{platform}
```

### Unregister

```
!unregister|{?game}
```

### Update

```
!update
```

