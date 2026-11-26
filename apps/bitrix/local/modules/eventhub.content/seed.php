<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$_SERVER['DOCUMENT_ROOT'] = '/www';
require_once '/www/bitrix/modules/main/include/prolog_before.php';

if (!\Bitrix\Main\Loader::includeModule('iblock')) {
    throw new RuntimeException('Модуль инфоблоков недоступен');
}

$type = 'eventhub';
if (!CIBlockType::GetByID($type)->Fetch()) {
    $types = new CIBlockType();
    if (!$types->Add(['ID' => $type, 'SECTIONS' => 'N', 'LANG' => [
        'ru' => ['NAME' => 'Контент EventHub'], 'en' => ['NAME' => 'EventHub'],
    ]])) {
        throw new RuntimeException($types->LAST_ERROR);
    }
}

$blocks = [];
foreach (['venues' => 'Площадки', 'speakers' => 'Спикеры', 'events' => 'Мероприятия'] as $code => $name) {
    $existing = CIBlock::GetList([], ['TYPE' => $type, '=CODE' => $code, 'CHECK_PERMISSIONS' => 'N'])->Fetch();
    $block = new CIBlock();
    $blocks[$code] = $existing['ID'] ?? $block->Add([
        'IBLOCK_TYPE_ID' => $type, 'CODE' => $code, 'NAME' => $name, 'SITE_ID' => ['s1'],
    ]);
    if (!$blocks[$code]) {
        throw new RuntimeException($block->LAST_ERROR);
    }
}

foreach ([
    'CITY' => ['Город'], 'STARTS_AT' => ['Начало с часовым смещением'],
    'TIMEZONE' => ['Часовой пояс'], 'PROGRAM' => ['Программа', 'MULTIPLE' => 'Y'],
    'VENUE' => ['Площадка', 'PROPERTY_TYPE' => 'E', 'LINK_IBLOCK_ID' => $blocks['venues']],
    'SPEAKERS' => ['Спикеры', 'PROPERTY_TYPE' => 'E', 'MULTIPLE' => 'Y', 'LINK_IBLOCK_ID' => $blocks['speakers']],
] as $code => $fields) {
    if (!CIBlockProperty::GetList([], ['IBLOCK_ID' => $blocks['events'], 'CODE' => $code])->Fetch()) {
        $name = $fields[0];
        unset($fields[0]);
        $property = new CIBlockProperty();
        if (!$property->Add($fields + ['IBLOCK_ID' => $blocks['events'], 'CODE' => $code, 'NAME' => $name, 'PROPERTY_TYPE' => 'S'])) {
            throw new RuntimeException($property->LAST_ERROR);
        }
    }
}

function ensureElement(int $block, string $id, string $name, array $fields = []): int
{
    $existing = CIBlockElement::GetList([], ['IBLOCK_ID' => $block, '=XML_ID' => $id], false, false, ['ID'])->Fetch();
    if ($existing) {
        return (int) $existing['ID'];
    }
    $element = new CIBlockElement();
    $result = $element->Add($fields + ['IBLOCK_ID' => $block, 'XML_ID' => $id, 'NAME' => $name]);
    if (!$result) {
        throw new RuntimeException($element->LAST_ERROR);
    }
    return (int) $result;
}

$venues = ['Мастерская на набережной' => 'print-workshop', 'Читальный зал' => 'reading-room', 'Мастерская' => 'workshop'];
$speakers = ['Анна Белова' => 'anna-belova', 'Олег Лапин' => 'oleg-lapin'];
foreach (json_decode(file_get_contents('/dist/events.json'), true, flags: JSON_THROW_ON_ERROR) as $event) {
    $venue = $event['venue'];
    ensureElement($blocks['events'], $event['id'], $event['title'], [
        'ACTIVE' => $event['published'] ? 'Y' : 'N', 'DETAIL_TEXT' => $event['description'],
        'PROPERTY_VALUES' => [
            'CITY' => $event['city'], 'STARTS_AT' => $event['startsAt'], 'TIMEZONE' => $event['timezone'],
            'PROGRAM' => $event['program'], 'VENUE' => ensureElement($blocks['venues'], $venues[$venue], $venue),
            'SPEAKERS' => array_map(fn ($name) => ensureElement($blocks['speakers'], $speakers[$name], $name), $event['speakers']),
        ],
    ]);
}
