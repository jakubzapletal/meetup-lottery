# Meetup.com Lottery

This mini PHP application provides simple lottery based on RSVPed members of some [Meetup.com](http://www.meetup.com/) event.

## Requirements

For run a lottery you need your API Key from [https://secure.meetup.com/meetup_api/key/](https://secure.meetup.com/meetup_api/key/) and ID of event, which you can find in URL of the event.

## Installing

### Download from GitHub

```
git clone git@github.com:jakubzapletal/meetup-lottery.git
```

### Install 3rd party dependencies via Composer

```
php composer.phar install
```

### Create config file

You need to create `config.yml` file in `app` directory. There is situated example file.

```
api_key: # your key from https://secure.meetup.com/meetup_api/key/
```

## Run

So now you can run application in browser

```
http://localhost/path/to/application
```

Enjoy this application and contribution is welcome.