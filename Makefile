COMPOSE ?= docker compose

.PHONY: up check down
up:
	$(COMPOSE) up --build -d
	@for attempt in 1 2 3 4 5 6; do \
		$(COMPOSE) exec -T api php bin/prepare.php && break; \
		[ "$$attempt" != 6 ] || exit 1; sleep 2; \
	done
	@for attempt in 1 2 3 4 5 6; do \
		$(MAKE) check && exit 0; \
		[ "$$attempt" != 6 ] || exit 1; sleep 2; \
	done

check:
	$(COMPOSE) exec -T api php bin/check.php

down:
	$(COMPOSE) down

BX := $(COMPOSE) -p eventhub-bitrix --env-file apps/bitrix/.local/settings.env -f apps/bitrix/compose.yaml
.PHONY: bitrix-up bitrix-check bitrix-down
bitrix-up:
	@sh apps/bitrix/bootstrap.sh
	$(BX) build cms wizard
	$(BX) up -d --wait --wait-timeout 180 db cms
	$(BX) run --rm --no-deps wizard install
	$(MAKE) bitrix-seed

.PHONY: bitrix-seed
bitrix-seed:
	$(BX) exec -T cms php83 -d short_open_tag=On /www/local/modules/eventhub.content/seed.php

bitrix-check:
	$(BX) run --rm --no-deps wizard check

bitrix-down:
	$(BX) down
