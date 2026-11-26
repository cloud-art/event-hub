#!/bin/sh
set -eu
if [ ! -f /www/.eventhub-unpacked ]; then
    tar -xzf /dist/business_encode.tar.gz -C /www
    touch /www/.eventhub-unpacked
fi
exec php83 -d short_open_tag=On -d date.timezone=Europe/Moscow \
    -d memory_limit=512M -d max_input_vars=10000 -S 0.0.0.0:8080 -t /www
