#!/bin/sh
set -eu
if [ "${CONTENT_SOURCE:-}" = mock ] && [ "${APP_ENV:-}" != local ]; then
    echo "Временный источник разрешён только в локальном режиме." >&2
    exit 1
fi
exec php -S 0.0.0.0:8080 -t public public/index.php
