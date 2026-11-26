#!/bin/sh
set -eu
cd "$(dirname "$0")"
umask 077
mkdir -p .local
if [ ! -s .local/settings.env ]; then
    if [ "${BITRIX_ACCEPT_LICENSE:-}" != 1 ]; then
        echo "Для первой установки подтвердите условия демоверсии: BITRIX_ACCEPT_LICENSE=1 make bitrix-up" >&2
        exit 1
    fi
    tmp=$(mktemp .local/settings.XXXXXX)
    for key in BX_DB_PASSWORD BX_ADMIN_PASSWORD; do
        printf '%s=%sAa1!\n' "$key" "$(od -An -N24 -tx1 /dev/urandom | tr -d ' \n')"
    done > "$tmp"
    mv "$tmp" .local/settings.env
fi
archive=.local/business_encode.tar.gz
if [ ! -f "$archive" ]; then
    curl --fail --location --silent --show-error --max-time 300 \
        --output "$archive.part" https://www.1c-bitrix.ru/download/files/business_encode.tar.gz
    mv "$archive.part" "$archive"
fi
printf '%s  %s\n' 776874b8d45ebae148d50a37b7059a8296dacc8ffc6e910fb4d49295910a268e "$archive" | sha256sum -c -
