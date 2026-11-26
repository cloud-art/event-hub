<?php

use App\Content\LocalEventSource;

require dirname(__DIR__, 2).'/api/src/Content/LocalEventSource.php';
$_SERVER['DOCUMENT_ROOT'] = '/www';
require '/www/bitrix/modules/main/include/prolog_before.php';
\Bitrix\Main\Loader::includeModule('iblock');

function readExport(): array
{
    ob_start();
    require '/www/local/modules/eventhub.content/export.php';
    $body = ob_get_clean();
    $batch = LocalEventSource::parse($body);
    foreach (json_decode($body, true, flags: JSON_THROW_ON_ERROR) as $row) {
        if ($row !== $batch[$row['id']]) {
            throw new RuntimeException('Выдача содержит поля вне контракта каталога.');
        }
    }
    return $batch;
}

$before = readExport();
$block = CIBlock::GetList([], ['TYPE' => 'eventhub', '=CODE' => 'events', 'CHECK_PERMISSIONS' => 'N'])->Fetch();
$row = CIBlockElement::GetList([], ['IBLOCK_ID' => $block['ID'], '=XML_ID' => 'print-studio'], false, false, ['ID'])->Fetch();
$db = \Bitrix\Main\Application::getConnection();
$db->startTransaction();
try {
    $title = 'Проверка выдачи '.bin2hex(random_bytes(4));
    $element = new CIBlockElement();
    if (!$element->Update($row['ID'], ['NAME' => $title, 'ACTIVE' => 'N'])) {
        throw new RuntimeException($element->LAST_ERROR);
    }
    $expected = $before;
    $expected['print-studio']['title'] = $title;
    $expected['print-studio']['published'] = false;
    if (readExport() !== $expected) {
        throw new RuntimeException('После правки изменился ID, состав партии или постороннее поле.');
    }
} finally {
    $db->rollbackTransaction();
}
if (readExport() !== $before) {
    throw new RuntimeException('Исходный контент не восстановлен.');
}
echo "Контракт, состав полей, постоянный ID и отмена тестовой правки проверены.\n";
