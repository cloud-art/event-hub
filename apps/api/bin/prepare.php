<?php

use App\Content\EventProjection;
use App\Content\EventBatch;
use App\Content\LocalEventSource;

require dirname(__DIR__).'/vendor/autoload.php';
try {
    $events = match (getenv('CONTENT_SOURCE')) {
        'mock' => LocalEventSource::read(),
        'bitrix' => EventBatch::parse(stream_get_contents(STDIN)),
        default => throw new RuntimeException('Неизвестный источник мероприятий.'),
    };
    $projection = new EventProjection();
    $projection->migrate();
    $projection->import($events);
    printf("Импортировано мероприятий: %d\n", count($events));
} catch (Throwable $error) {
    fwrite(STDERR, "Ошибка подготовки: ".$error->getMessage()."\n");
    exit(1);
}
