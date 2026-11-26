<?php

namespace App\Content;

final class LocalEventSource
{
    public static function read(): array
    {
        if (getenv('APP_ENV') !== 'local' || getenv('CONTENT_SOURCE') !== 'mock') {
            throw new \RuntimeException('Временный источник разрешён только в локальном режиме.');
        }
        $body = file_get_contents('http://source/events.json', false, stream_context_create([
            'http' => ['timeout' => 5, 'follow_location' => 0],
        ]));
        if ($body === false) {
            throw new \RuntimeException('Источник мероприятий недоступен.');
        }
        return EventBatch::parse($body);
    }
}
