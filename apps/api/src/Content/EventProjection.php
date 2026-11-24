<?php

namespace App\Content;

final class EventProjection
{
    private \PDO $db;

    public function __construct(?\PDO $db = null)
    {
        $this->db = $db ?? new \PDO(getenv('DB_DSN'), getenv('DB_USER'), getenv('DB_PASSWORD'));
    }

    public function migrate(): void
    {
        $this->db->exec(file_get_contents(__DIR__.'/../../migrations/001_events.sql'));
    }

    public function import(array $events): void
    {
        $this->db->beginTransaction();
        try {
            $upsert = $this->db->prepare('INSERT INTO events (id, published, content) VALUES (?, ?, ?)
                ON CONFLICT (id) DO UPDATE SET published = excluded.published, content = excluded.content');
            foreach ($events as $event) {
                $published = $event['published'];
                unset($event['published']);
                $upsert->execute([$event['id'], $published ? 'true' : 'false', json_encode($event, JSON_THROW_ON_ERROR)]);
            }
            $this->db->commit();
        } catch (\Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
    }

    public function published(): array
    {
        $rows = $this->db->query("SELECT content FROM events WHERE published ORDER BY (content->>'startsAt')::timestamptz, id");
        return array_map(fn ($v) => json_decode($v, true, flags: JSON_THROW_ON_ERROR), $rows->fetchAll(\PDO::FETCH_COLUMN));
    }

    public function find(string $id): ?array
    {
        $query = $this->db->prepare('SELECT content FROM events WHERE published AND id = ?');
        $query->execute([$id]);
        $value = $query->fetchColumn();
        return $value === false ? null : json_decode($value, true, flags: JSON_THROW_ON_ERROR);
    }
}
