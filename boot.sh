#!/bin/bash

mkdir migrations
php vendor/bin/doctrine-migrations generate --no-interaction
php vendor/bin/doctrine-migrations diff --no-interaction
php vendor/bin/doctrine-migrations migrate --no-interaction


apache2-foreground