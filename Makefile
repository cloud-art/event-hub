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
