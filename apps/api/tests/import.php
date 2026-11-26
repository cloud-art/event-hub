<?php

use App\Content\EventProjection;
use App\Content\EventBatch;
use App\Content\LocalEventSource;

require dirname(__DIR__).'/vendor/autoload.php';

$db = new PDO(getenv('DB_DSN'), getenv('DB_USER'), getenv('DB_PASSWORD'));
// Временная таблица скрывает рабочую проекцию только в этом соединении.
$db->exec('CREATE TEMP TABLE events (LIKE public.events INCLUDING ALL)');
$projection = new EventProjection($db);
assert($projection->published() === []);
$batch = LocalEventSource::read();
$projection->import($batch);
$projection->import($batch);
assert(count($projection->published()) === 2);
assert($projection->find('draft-session') === $projection->find('unknown'));
$batch['print-studio']['title'] = 'Новое название';
$projection->import($batch);
assert($projection->find('print-studio')['title'] === 'Новое название');
$invalid = array_values($batch);
$invalid[0]['title'] = 'Не сохранять';
$invalid[1]['program'] = null;
try {
    $projection->import(EventBatch::parse(json_encode($invalid, JSON_THROW_ON_ERROR)));
    throw new RuntimeException('Ошибка партии не обнаружена.');
} catch (UnexpectedValueException) {
    assert($projection->find('print-studio')['title'] === 'Новое название');
}
echo "Импорт, обновление, отклонение неверной партии и публичность проверены.\n";
