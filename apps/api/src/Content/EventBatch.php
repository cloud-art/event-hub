<?php

namespace App\Content;

final class EventBatch
{
    public static function parse(string $body): array
    {
        $batch = json_decode($body, false, flags: JSON_THROW_ON_ERROR);
        if (!is_array($batch)) {
            throw new \UnexpectedValueException('Ожидался список мероприятий.');
        }
        $events = [];
        foreach ($batch as $row) {
            foreach (['id', 'title', 'city', 'venue', 'description', 'startsAt', 'timezone'] as $key) {
                if (!is_string($row->$key ?? null) || trim($row->$key) === '') {
                    throw new \UnexpectedValueException("Некорректное поле: $key");
                }
            }
            if (!preg_match('/^[a-z0-9-]+$/D', $row->id) || isset($events[$row->id]) || !is_bool($row->published ?? null)) {
                throw new \UnexpectedValueException('Ошибка идентификатора или признака публикации.');
            }
            foreach (['program', 'speakers'] as $key) {
                if (!is_array($row->$key ?? null) || array_filter($row->$key, fn ($v) => !is_string($v) || trim($v) === '')) {
                    throw new \UnexpectedValueException("Некорректный список: $key");
                }
            }
            $start = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $row->startsAt);
            if (!$start || $start->format(\DateTimeInterface::ATOM) !== $row->startsAt
                || !in_array($row->timezone, \DateTimeZone::listIdentifiers(), true)) {
                throw new \UnexpectedValueException('Некорректная дата или часовой пояс.');
            }
            $events[$row->id] = array_intersect_key((array) $row, array_flip([
                'id', 'title', 'city', 'venue', 'description', 'startsAt', 'timezone', 'program', 'speakers', 'published',
            ]));
        }
        return $events;
    }
}
