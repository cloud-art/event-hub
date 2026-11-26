#!/bin/sh
set -eu

# Выполнять из корня после первого bitrix-import.
catalog() {
    ${COMPOSE:-docker compose} -p eventhub-bitrix-catalog -f compose.yaml -f compose.bitrix-catalog.yaml "$@"
}
snapshot() {
    catalog exec -T db psql -XAt -U eventhub -c 'SELECT id, published, content FROM events ORDER BY id'
}
before=$(snapshot)
test -n "$before"
make bitrix-import
test "$(snapshot)" = "$before"

# Первая строка валидна, но не должна попасть в базу, если вторая испорчена.
if printf '%s\n' '[{"id":"rollback-test","published":true,"title":"Тест","city":"Тест","venue":"Зал","description":"Тест","startsAt":"2027-01-01T10:00:00+03:00","timezone":"Europe/Moscow","program":[],"speakers":[]},{}]' |
    catalog exec -T api php bin/prepare.php; then
    exit 1
fi
test "$(snapshot)" = "$before"

if make bitrix-import BX="${COMPOSE:-docker compose} -p eventhub-unavailable-test --env-file apps/bitrix/.local/settings.env -f apps/bitrix/compose.yaml"; then
    exit 1
fi
test "$(snapshot)" = "$before"
echo "Повторный импорт и сохранение каталога при обеих ошибках проверены."
