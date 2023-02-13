#!/bin/sh -eu

# composer requires HOME to be available in environment.
export HOME="/home/$(whoami)"
PATH="$HOME/bin:$PATH"
PHP="/usr/bin/php"
COMPOSER="$PHP $HOME/bin/composer --working-dir=$PWD"

DIR="$(cd "$(dirname "$0")" && pwd)"
REQUEST_PATH="$DIR/var/deploy.request"
LOCK_PATH="$DIR/var/deploy.lock"

if [ ! -f "$REQUEST_PATH" ]; then
    # If no request has been made, take no action. This is not an error.
    exit 0
fi

if [ -f "$LOCK_PATH" ]; then
    echo "Deployment is locked. Remove file ${LOCK_PATH} to release the lock."
    exit 1
fi

echo "Deployment started at [$(date)]"

(
    flock -x -n 9

    # Commands to run
    echo "Deployment runs git-pull at [$(date)]"
    git pull
    echo "Deployment runs 'composer install' at [$(date)]"
    $COMPOSER install
    echo "Deployment runs 'npm ci' at [$(date)]"
    npm ci
    echo "Deployment runs 'npm run build' at [$(date)]"
    npm run build

) 9>"$LOCK_PATH"
rm -f "$LOCK_PATH"
rm -f "$REQUEST_PATH"

echo "'Deployment finished at ['$(date)'].'"
exit 0
