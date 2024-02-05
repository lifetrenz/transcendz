#!/bin/sh

# if [ -z ${APP_ENV+x} ];
# then
# . ./.env
# fi

# if [ "$APP_ENV" != "prod" ];
# then
    [ ! -d ".git/hooks" ] && mkdir -p .git/hooks;
    cp contrib/pre-commit .git/hooks/pre-commit;
    chmod +x .git/hooks/pre-commit;
# fi
