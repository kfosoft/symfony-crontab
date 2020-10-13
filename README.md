# Symfony Crontab
## Installation

Installation with Composer

Either run
```
composer require kfosoft/symfony-crontab
```

## Configuring
You have to add crontab configuration into `config/packages`
Example file:
```
crontab:
  tab:
    # Internal job without params
    SomeJob:
      type: 'internal'
      command: 'symfony:command:name'
      expression: '*/1 * * * *'
    # Internal job with params
    SomeJobWithParams:
      type: 'internal'
      command: 'symfony:command:name'
      expression: '*/1 * * * *'
      params:
        argument1: 'argument'
        --option1: 'long option'
        -t: 'short option'
    # External job example
    ExternalSomeJob:
      type: 'external'
      command: '/bin/bash test -a --opt=123'
      expression: '*/1 * * * *'
```

## Using
```
bin/console cron:daemon
```

## Supervisor config
```
[program:symfony-cron]
command=/fullpath/to/bin/console cron:daemon 
autostart=true
autorestart=true
user=user_name_or_id
redirect_stderr=true
```