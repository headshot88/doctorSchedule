<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;

Loader::includeModule('highloadblock');

$hlData = [
    'NAME'        => 'DoctorSchedule',
    'TABLE_NAME'  => 'b_hl_doctor_schedule',
];

$result = HighloadBlockTable::add($hlData);
if (!$result->isSuccess()) {
    die('Ошибка создания HL-блока: ' . implode(', ', $result->getErrorMessages()));
}

$hlId = $result->getId();

$userTypeEntity = new CUserTypeEntity();

$userTypeEntity->Add([
    'ENTITY_ID'     => 'HLBLOCK_' . $hlId,
    'FIELD_NAME'    => 'UF_USER_ID',
    'USER_TYPE_ID'  => 'integer',
    'XML_ID'        => '',
    'SORT'          => 100,
    'MULTIPLE'      => 'N',
    'MANDATORY'     => 'Y',
    'SHOW_FILTER'   => 'I',
    'SHOW_IN_LIST'  => 'Y',
    'EDIT_IN_LIST'  => 'Y',
    'IS_SEARCHABLE' => 'N',
    'SETTINGS'      => [
        'DEFAULT_VALUE' => 0,
    ],
]);

$dayValues = [
    ['VALUE' => 'Понедельник', 'SORT' => 100, 'XML_ID' => 'mon'],
    ['VALUE' => 'Вторник',     'SORT' => 200, 'XML_ID' => 'tue'],
    ['VALUE' => 'Среда',       'SORT' => 300, 'XML_ID' => 'wed'],
    ['VALUE' => 'Четверг',     'SORT' => 400, 'XML_ID' => 'thu'],
    ['VALUE' => 'Пятница',     'SORT' => 500, 'XML_ID' => 'fri'],
    ['VALUE' => 'Суббота',     'SORT' => 600, 'XML_ID' => 'sat'],
    ['VALUE' => 'Воскресенье', 'SORT' => 700, 'XML_ID' => 'sun'],
];

$userTypeEntity->Add([
    'ENTITY_ID'     => 'HLBLOCK_' . $hlId,
    'FIELD_NAME'    => 'UF_DAY_OF_WEEK',
    'USER_TYPE_ID'  => 'enumeration',
    'SORT'          => 200,
    'MULTIPLE'      => 'N',
    'MANDATORY'     => 'Y',
    'SHOW_FILTER'   => 'I',
    'SHOW_IN_LIST'  => 'Y',
    'EDIT_IN_LIST'  => 'Y',
    'IS_SEARCHABLE' => 'N',
    'SETTINGS'      => [
        'DISPLAY'          => 'LIST',
        'LIST_HEIGHT'      => 1,
        'CAPTION_NO_VALUE' => '',
    ],
    'ENUM_VALUES'   => $dayValues,
]);

$userTypeEntity->Add([
    'ENTITY_ID'     => 'HLBLOCK_' . $hlId,
    'FIELD_NAME'    => 'UF_IS_WORKDAY',
    'USER_TYPE_ID'  => 'boolean',
    'SORT'          => 300,
    'MULTIPLE'      => 'N',
    'MANDATORY'     => 'Y',
    'SHOW_FILTER'   => 'I',
    'SHOW_IN_LIST'  => 'Y',
    'EDIT_IN_LIST'  => 'Y',
    'IS_SEARCHABLE' => 'N',
    'SETTINGS'      => [
        'DEFAULT_VALUE' => 0,
        'DISPLAY'       => 'CHECKBOX',
    ],
]);

$userTypeEntity->Add([
    'ENTITY_ID'     => 'HLBLOCK_' . $hlId,
    'FIELD_NAME'    => 'UF_TIME_FROM',
    'USER_TYPE_ID'  => 'string',
    'SORT'          => 400,
    'MULTIPLE'      => 'N',
    'MANDATORY'     => 'N',
    'SHOW_FILTER'   => 'I',
    'SHOW_IN_LIST'  => 'Y',
    'EDIT_IN_LIST'  => 'Y',
    'IS_SEARCHABLE' => 'N',
    'SETTINGS'      => [
        'SIZE'          => 20,
        'ROWS'          => 1,
        'REGEXP'        => '',
        'MIN_LENGTH'    => 0,
        'MAX_LENGTH'    => 5,
        'DEFAULT_VALUE' => '',
    ],
]);

$userTypeEntity->Add([
    'ENTITY_ID'     => 'HLBLOCK_' . $hlId,
    'FIELD_NAME'    => 'UF_TIME_TO',
    'USER_TYPE_ID'  => 'string',
    'SORT'          => 500,
    'MULTIPLE'      => 'N',
    'MANDATORY'     => 'N',
    'SHOW_FILTER'   => 'I',
    'SHOW_IN_LIST'  => 'Y',
    'EDIT_IN_LIST'  => 'Y',
    'IS_SEARCHABLE' => 'N',
    'SETTINGS'      => [
        'SIZE'          => 20,
        'ROWS'          => 1,
        'REGEXP'        => '',
        'MIN_LENGTH'    => 0,
        'MAX_LENGTH'    => 5,
        'DEFAULT_VALUE' => '',
    ],
]);

echo 'Highload-блок успешно создан с ID ' . $hlId;
