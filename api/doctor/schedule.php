<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('NO_AGENT_CHECK', true);
define('NEED_AUTH', false);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Context;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Content-Type: application/json; charset=utf-8');

if (!Loader::includeModule('highloadblock')) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Highloadblock module not loaded']);
    die();
}

$request = Context::getCurrent()->getRequest();
$doctorId = (int)$request->getQuery('doctor_id');

if ($doctorId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid doctor_id']);
    die();
}

$hlblock = HighloadBlockTable::getList(['filter' => ['=NAME' => 'DoctorSchedule']])->fetch();
if (!$hlblock) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'HL-block not found']);
    die();
}

$entity = HighloadBlockTable::compileEntity($hlblock);
$entityDataClass = $entity->getDataClass();

$scheduleDb = [];
$rs = $entityDataClass::getList([
    'filter' => ['=UF_USER_ID' => $doctorId],
    'order'  => ['UF_DAY_OF_WEEK' => 'ASC'], 
]);
while ($row = $rs->fetch()) {
    $scheduleDb[$row['UF_DAY_OF_WEEK']] = $row;
}

$daysOrder = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
$daysNames = [
    'mon' => 'Понедельник',
    'tue' => 'Вторник',
    'wed' => 'Среда',
    'thu' => 'Четверг',
    'fri' => 'Пятница',
    'sat' => 'Суббота',
    'sun' => 'Воскресенье',
];

$data = [];
foreach ($daysOrder as $xmlId) {
    $dayData = $scheduleDb[$xmlId] ?? null;
    
    $isWorkday = $dayData ? (bool)$dayData['UF_IS_WORKDAY'] : false;
    
    $data[] = [
        'day'        => $daysNames[$xmlId],
        'day_xml'    => $xmlId,
        'is_workday' => $isWorkday,
        'time_from'  => $isWorkday ? $dayData['UF_TIME_FROM'] : null,
        'time_to'    => $isWorkday ? $dayData['UF_TIME_TO'] : null,
    ];
}

echo json_encode([
    'success' => true,
    'data'    => $data,
]);
