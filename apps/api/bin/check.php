<?php

function get(string $url, string $step): string
{
    $body = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 8]]));
    if ($body === false) {
        throw new RuntimeException("$step: нет ответа от $url");
    }
    return $body;
}

try {
    get('http://source/events.json', 'Источник');
    $events = json_decode(get('http://api:8080/api/v1/events', 'API'), true, flags: JSON_THROW_ON_ERROR);
    if (!isset($events[0]['id'])) {
        throw new RuntimeException('Импорт: нет опубликованных мероприятий.');
    }
    $path = '/events/'.rawurlencode($events[0]['id']);
    $event = json_decode(get('http://api:8080/api/v1'.$path, 'API мероприятия'), true, flags: JSON_THROW_ON_ERROR);
    if (!str_contains(get('http://web:3000/', 'Каталог'), 'href="'.$path.'"')) {
        throw new RuntimeException('Каталог: нет ссылки на импортированное мероприятие.');
    }
    if (!str_contains(get('http://web:3000'.$path, 'Страница мероприятия'), htmlspecialchars($event['title']))) {
        throw new RuntimeException('Страница мероприятия: нет импортированного названия.');
    }
    echo "Источник, API, каталог и страница мероприятия доступны.\n";
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage()."\n");
    exit(1);
}
