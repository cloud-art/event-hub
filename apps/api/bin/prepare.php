<?php

use App\Content\EventProjection;
use App\Content\LocalEventSource;

require dirname(__DIR__).'/vendor/autoload.php';
try {
    $projection = new EventProjection();
    $projection->migrate();
    $events = LocalEventSource::read();
    $projection->import($events);
    printf("Импортировано мероприятий: %d\n", count($events));
} catch (Throwable $error) {
    fwrite(STDERR, "Ошибка подготовки: ".$error->getMessage()."\n");
    exit(1);
}
