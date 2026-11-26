<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

try {
    $_SERVER['DOCUMENT_ROOT'] = '/www';
    require_once '/www/bitrix/modules/main/include/prolog_before.php';
    if (!\Bitrix\Main\Loader::includeModule('iblock')) {
        throw new RuntimeException('Модуль инфоблоков недоступен.');
    }

    $blocks = [];
    $list = CIBlock::GetList([], ['TYPE' => 'eventhub', 'CHECK_PERMISSIONS' => 'N']);
    while ($block = $list->Fetch()) {
        $blocks[$block['CODE']] = $block['ID'];
    }
    foreach (['venues', 'speakers', 'events'] as $code) {
        if (!isset($blocks[$code])) {
            throw new RuntimeException("Не найден инфоблок: $code");
        }
    }
    $names = [];
    foreach (['venues', 'speakers'] as $code) {
        $rows = CIBlockElement::GetList([], ['IBLOCK_ID' => $blocks[$code]], false, false, ['ID', 'NAME']);
        while ($row = $rows->Fetch()) {
            $names[$code][$row['ID']] = $row['NAME'];
        }
    }

    $events = [];
    $rows = CIBlockElement::GetList(['ID' => 'ASC'], ['IBLOCK_ID' => $blocks['events']], false, false,
        ['ID', 'XML_ID', 'NAME', 'ACTIVE', 'DETAIL_TEXT']);
    while ($row = $rows->Fetch()) {
        $values = [];
        $props = CIBlockElement::GetProperty($blocks['events'], $row['ID'], ['sort' => 'asc', 'value_id' => 'asc']);
        while ($prop = $props->Fetch()) {
            if ($prop['VALUE'] !== null && $prop['VALUE'] !== '') {
                $values[$prop['CODE']][] = $prop['VALUE'];
            }
        }
        $events[] = [
            'id' => $row['XML_ID'], 'published' => $row['ACTIVE'] === 'Y', 'title' => $row['NAME'],
            'city' => $values['CITY'][0] ?? null, 'startsAt' => $values['STARTS_AT'][0] ?? null,
            'timezone' => $values['TIMEZONE'][0] ?? null, 'description' => $row['DETAIL_TEXT'],
            'venue' => $names['venues'][$values['VENUE'][0] ?? ''] ?? null,
            'program' => $values['PROGRAM'] ?? [],
            'speakers' => array_map(fn ($id) => $names['speakers'][$id] ?? null, $values['SPEAKERS'] ?? []),
        ];
    }
    echo json_encode($events, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), "\n";
} catch (Throwable $error) {
    fwrite(STDERR, "Не удалось выгрузить мероприятия: {$error->getMessage()}\n");
    exit(1);
}
