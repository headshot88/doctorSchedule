<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Context;

global $USER;

if (!$USER->IsAuthorized()) {
    LocalRedirect('/auth/'); 
}


Loader::includeModule('highloadblock');

$hlblock = HighloadBlockTable::getList(['filter' => ['=NAME' => 'DoctorSchedule']])->fetch();
if (!$hlblock) {
    echo 'Ошибка: HL-блок не найден.';
    die();
}
$entity = HighloadBlockTable::compileEntity($hlblock);
$entityDataClass = $entity->getDataClass();

$userId = $USER->GetID();

$request = Context::getCurrent()->getRequest();
if ($request->isPost() && check_bitrix_sessid()) {
    $scheduleData = $request->getPost('schedule');
    if (is_array($scheduleData)) {
        foreach ($scheduleData as $dayXmlId => $data) {
            $isWork = ($data['is_work'] === 'Y');
            $fields = [
                'UF_USER_ID'     => $userId,
                'UF_DAY_OF_WEEK' => $dayXmlId,
                'UF_IS_WORKDAY'  => $isWork,
                'UF_TIME_FROM'   => $isWork ? trim($data['from']) : '',
                'UF_TIME_TO'     => $isWork ? trim($data['to']) : '',
            ];

            $existing = $entityDataClass::getList([
                'filter' => [
                    '=UF_USER_ID'     => $userId,
                    '=UF_DAY_OF_WEEK' => $dayXmlId,
                ],
                'limit' => 1
            ])->fetch();

            if ($existing) {
                $entityDataClass::update($existing['ID'], $fields);
            } else {
                $entityDataClass::add($fields);
            }
        }
        LocalRedirect($request->getRequestUri());
    }
}


$scheduleDb = [];
$rs = $entityDataClass::getList([
    'filter' => ['=UF_USER_ID' => $userId],
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

?>

<div class="doctor-schedule">
    <h1>Настройка расписания работы</h1>

    <form method="post" action="">
        <?= bitrix_sessid_post() ?>

        <table class="schedule-table">
            <thead>
                <tr>
                    <th>День недели</th>
                    <th>Рабочий день</th>
                    <th>Начало</th>
                    <th>Окончание</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daysOrder as $dayXml): ?>
                    <?php
                        $dayData = $scheduleDb[$dayXml] ?? null;
                        $isWork = $dayData ? ($dayData['UF_IS_WORKDAY'] == 1) : false;
                        $timeFrom = $dayData ? htmlspecialcharsbx($dayData['UF_TIME_FROM']) : '09:00';
                        $timeTo   = $dayData ? htmlspecialcharsbx($dayData['UF_TIME_TO'])   : '18:00';
                    ?>
                    <tr>
                        <td><?= $daysNames[$dayXml] ?></td>
                        <td>
                            <input type="checkbox"
                                   name="schedule[<?= $dayXml ?>][is_work]"
                                   value="Y"
                                   <?= $isWork ? 'checked' : '' ?>
                                   class="workday-checkbox"
                                   data-day="<?= $dayXml ?>">
                        </td>
                        <td>
                            <input type="text"
                                   name="schedule[<?= $dayXml ?>][from]"
                                   value="<?= $timeFrom ?>"
                                   placeholder="09:00"
                                   class="time-input"
                                   data-day="<?= $dayXml ?>"
                                   <?= !$isWork ? 'disabled' : '' ?>>
                        </td>
                        <td>
                            <input type="text"
                                   name="schedule[<?= $dayXml ?>][to]"
                                   value="<?= $timeTo ?>"
                                   placeholder="18:00"
                                   class="time-input"
                                   data-day="<?= $dayXml ?>"
                                   <?= !$isWork ? 'disabled' : '' ?>>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button type="submit" class="btn btn-primary">Сохранить расписание</button>
    </form>
</div>

<script>
    document.querySelectorAll('.workday-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            var day = this.dataset.day;
            var fromInput = document.querySelector('input[name="schedule[' + day + '][from]"]');
            var toInput = document.querySelector('input[name="schedule[' + day + '][to]"]');
            if (this.checked) {
                fromInput.disabled = false;
                toInput.disabled = false;
            } else {
                fromInput.disabled = true;
                toInput.disabled = true;
            }
        });
    });
</script>

<style>
    .doctor-schedule {
        max-width: 600px;
        margin: 20px auto;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .schedule-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .schedule-table th,
    .schedule-table td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }
    .schedule-table th {
        background-color: #f5f5f5;
    }
    .time-input {
        width: 80px;
        padding: 5px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    .btn-primary {
        background-color: #007bff;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .btn-primary:hover {
        background-color: #0069d9;
    }
</style>

<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
