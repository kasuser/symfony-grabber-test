## Installation

```bash
$ docker-compose build
$ docker-compose up
```

Endpoint on 80 port.

## Start worker

```bash
$ bin/console enqueue:consume --setup-broker -vvv
```
