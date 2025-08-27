<?php
include '../config/connection.php';
include '../includes/header.php';

$form_picture = isset($_SESSION['form_picture']) ? $_SESSION['form_picture'] : '';
$defaultImage = '../../assets/imgs/user_default.png';

if (!empty($form_picture)) {
    $form_picture = 'data:image/jpeg;base64,' . base64_encode($form_picture);
} else {
    // Fallback to default image
    $form_picture = $defaultImage;
}

$employee_id = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : '';
$employeename = isset($_SESSION['employeename']) ? $_SESSION['employeename'] : '';
$department = isset($_SESSION['department']) ? $_SESSION['department'] : '';
$area = isset($_SESSION['area']) ? $_SESSION['area'] : '';
$position = isset($_SESSION['position']) ? $_SESSION['position'] : '';
?>

<?php
// Get current month/year from POST or default to today
$month = $_POST['month'] ?? date('n'); // 1-12
$year = $_POST['year'] ?? date('Y');

$firstDay = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDay);
$monthName = date('F', $firstDay);
$dayOfWeek = date('w', $firstDay);
?>

<?php
// ================= fetch holidays ==================
$holidays = [];
$sql = "CALL WEB_SC_HOLIDAY_LIST(?, ?)";
if ($stmt = $conn2->prepare($sql)) {
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        // Store by date in format YYYY-MM-DD
        $holidays[$row['date']] = [
            'name' => $row['holiday'],
            'desc' => $row['description'],
            'type' => $row['type'] // add holiday type here
        ];
    }
    $stmt->close();
}
?>

<?php
// ================= Fetch Disabled Days ==================
$disabledDays = [];
$sql = "CALL WEB_SC_DISABLEDDAY_LIST(?, ?)";
$stmt = $conn2->prepare($sql);
$stmt->bind_param("ii", $month, $year);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $disabledDays[$row['date']] = 1;
}
$res->close();
$stmt->close();
$conn2->next_result(); // clear multi query buffer
?>

<style>
    .calendar {
        table-layout: fixed;
        width: 100%;
    }

    .calendar td {
        height: 80px;
        vertical-align: top;
        cursor: pointer;
        user-select: none;
        overflow: hidden;
    }

    .calendar td span.today {
        user-select: none;
        display: inline-block;
        width: 35px;
        height: 35px;
        line-height: 35px;
        text-align: center;
        border-radius: 100%;
        background-color: #0d6efd;
        color: white;
    }

    .calendar td.selected {
        background-color: #ffc107 !important;
        color: #000;
    }

    .calendar td.holiday {
        text-align: center;
        vertical-align: top;
    }

    .calendar td.holiday.legal {
        background-color: #ff9999;
        /* light red for legal holiday */
        color: #900;
    }

    .calendar td.holiday.special {
        background-color: #ffd699;
        /* light orange for special holiday */
        color: #663300;
    }

    .calendar td.today {
        border: 2px solid #007bff;
        font-weight: bold;
    }

    .calendar td small {
        display: block;
        font-size: 0.6rem;
        line-height: 1.1;
    }

    .calendar-day.disabled-day {
        background-color: #f0f0f0;
        color: #aaa;
        cursor: not-allowed;
    }

    /* SHIFT TABLE */
    .schedule-row {
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.65rem;
    }

    .schedule-row:hover td:not(:nth-child(2)) {
        background-color: #f1f5f9;
    }

    .schedule-row.selected td:not(:nth-child(2)) {
        background-color: #0d6efd;
        color: white;
    }

    .schedule-row td:nth-child(2) {
        color: #fff;
        font-weight: bold;
    }
</style>

<div class="container-fluid mb-2">
    <div class="card shadow rounded-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center mb-3">
                <a href="shiftschedule_employee_list.php" class="btn btn-outline-secondary btn-sm rounded-4">
                    ← Back
                </a>
                <h5 class="mb-0 ms-3 text-muted text-center">Set Shift Schedule</h5>
            </div>

            <div class="col-12 mb-3">
                <div class="row g-3 align-items-start">
                    <!-- Employee Photo -->
                    <div class="col-12 col-sm-1 text-center text-sm-start">
                        <img src="<?= $form_picture ?>" alt="Employee Photo"
                            class="border border-1 border-muted rounded mb-2"
                            style="width:100px; height:100px; object-fit:cover;">
                    </div>

                    <!-- Employee Info -->
                    <div class="col-12 col-sm-6">
                        <div class="row mb-1">
                            <div class="col-4 fw-bold">Employee:</div>
                            <div class="col-8" id="modal-employee-info">[<?= $employee_id ?>] <?= $employeename ?></div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-4 fw-bold">Area/Section:</div>
                            <div class="col-8" id="modal-area"><?= $area ?></div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-4 fw-bold">Department:</div>
                            <div class="col-8" id="modal-department"><?= $department ?></div>
                        </div>
                        <div class="row">
                            <div class="col-4 fw-bold">Position:</div>
                            <div class="col-8" id="modal-position"><?= $position ?></div>
                        </div>
                    </div>

                    <!-- Instructions & Legends -->
                    <div class="col-12 col-sm-5">
                        <div class="card border-info rounded-4 h-100">
                            <div class="card-body p-3">
                                <h6 class="fw-bold text-info">Shortcuts</h6>
                                <ul class="mb-2 small">
                                    <li><strong>Multiselect Dates:</strong> Hold <kbd>CTRL</kbd> to select multiple dates.</li>
                                    <li><strong>Date Range Select:</strong> Click 1 date, hold <kbd>SHIFT</kbd>, then click the last date.</li>
                                    <li><strong>View Holiday Description:</strong> Hover on a date marked as holiday.</li>
                                    <li><strong>Deselect Date:</strong> Press <kbd>ESC</kbd> to deselect the selected date(s).</li>
                                </ul>

                                <h6 class="fw-bold text-info">Legends</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge" style="background-color: #ffc107; color: Black;">Selected Date</span>
                                    <span class="badge border border-primary text-primary" style="background-color: transparent;">Today</span>
                                    <span class="badge" style="background-color: #ffd699; color: #663300;">Special Holiday</span>
                                    <span class="badge" style="background-color: #ff9999; color: #900;">Legal Holiday</span>
                                    <span class="badge border border-dark" style="background-color: #f0f0f0; color: #aaa;">Disabled Date</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-md-12">
                <div class="row">
                    <!-- Left side: Shift Codes -->
                    <!-- Right side: Calendar -->
                    <div class="col-md-9">
                        <table class="table table-bordered calendar text-center">
                            <thead class="table-light">
                                <tr>
                                    <th colspan="7" class="text-center fs-4 fw-bold bg-light">
                                        <?= $monthName ?> <?= $year ?>
                                    </th>
                                </tr>
                                <tr>
                                    <th>Sunday</th>
                                    <th>Monday</th>
                                    <th>Tuesday</th>
                                    <th>Wednesday</th>
                                    <th>Thursday</th>
                                    <th>Friday</th>
                                    <th>Saturday</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <?php
                                    for ($i = 0; $i < $dayOfWeek; $i++) echo "<td></td>";

                                    $currentDay = 1;
                                    while ($currentDay <= $daysInMonth) {
                                        $dateStr = sprintf("%04d-%02d-%02d", $year, $month, $currentDay);
                                        $isToday = ($currentDay == date('j') && $month == date('n') && $year == date('Y'));

                                        $shiftcode = "NS";
                                        $holidayname = "";
                                        $cellClass = $isToday ? "today" : "";
                                        $cellContent = $currentDay;
                                        $titleAttr = "";

                                        // Check if Disabled
                                        $isDisabled = isset($disabledDays[$dateStr]);
                                        if ($isDisabled) {
                                            $cellClass .= " disabled-day";
                                        }

                                        // If Holiday
                                        if (isset($holidays[$dateStr])) {
                                            $type = strtolower($holidays[$dateStr]['type']);
                                            $shiftcode = "NS";
                                            $holidayname .= "{$holidays[$dateStr]['name']}";
                                            $cellClass .= " holiday $type";
                                            $cellContent .= "<br><small>{$holidays[$dateStr]['name']} ({$holidays[$dateStr]['type']})</small>";
                                            $titleAttr = htmlspecialchars($holidays[$dateStr]['desc']);
                                        }

                                        echo "<td class='calendar-day $cellClass'
                                        data-date='$dateStr' 
                                        data-shiftcode= '$shiftcode' 
                                        data-holidayname='$holidayname'
                                        title='$titleAttr'
                                        onclick='selectDate(event)'>$cellContent</td>";

                                        if (($currentDay + $dayOfWeek) % 7 == 0) echo "</tr><tr>";

                                        $currentDay++;
                                    }

                                    $remaining = (7 - (($daysInMonth + $dayOfWeek) % 7)) % 7;
                                    for ($i = 0; $i < $remaining; $i++) echo "<td></td>";
                                    ?>
                                </tr>

                            </tbody>
                        </table>
                        <p class="d-none">Selected Dates: <span id="selectedDates">None</span></p>
                    </div>

                    <div class="col-md-3">
                        <!-- Search bar -->
                        <input type="text" id="scheduleSearch" class="form-control mb-2 rounded-4" placeholder="Search schedules...">

                        <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                            <table class="table table-bordered table-hover text-center align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th colspan="5" class="fw-bold">Schedules</th>
                                    </tr>
                                    <tr class="small">
                                        <th style="width: 5%; position: sticky; top: 0; background: #f8f9fa; z-index: 2;"></th>
                                        <th style="width: 15%; position: sticky; top: 0; background: #f8f9fa; z-index: 2;">Code</th>
                                        <th style="width: 25%; position: sticky; top: 0; background: #f8f9fa; z-index: 2;">Start</th>
                                        <th style="width: 25%; position: sticky; top: 0; background: #f8f9fa; z-index: 2;">End</th>
                                        <th style="width: 30%; position: sticky; top: 0; background: #f8f9fa; z-index: 2;">Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $conn2->prepare("CALL WEB_SC_SCHEDULE_LIST(?)");
                                    $stmt->bind_param("s", $area);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    if ($result) {
                                        while ($row = $result->fetch_assoc()) {
                                            $color = $row['shiftcolor_hex'];
                                            $tdStyle = $color ? "style='background-color: {$color};'" : "";
                                            echo "<tr class='schedule-row small' data-color='{$row['shiftcolor_hex']}' data-code='{$row['shiftcode']}' data-start='{$row['shiftstart']}' data-end='{$row['shiftend']}' data-desc='{$row['shiftdescription']}'>";
                                            echo "<td><input type='radio' name='schedule_select' value='{$row['shiftcode']}'></td>";
                                            echo "<td $tdStyle>{$row['shiftcode']}</td>";
                                            echo "<td>{$row['shiftstart']}</td>";
                                            echo "<td>{$row['shiftend']}</td>";
                                            echo "<td>{$row['shiftdescription']}</td>";
                                            echo "</tr>";
                                        }
                                        $result->close();
                                    }
                                    $stmt->close();
                                    $conn2->next_result();
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button id="resetShiftsBtn" class="btn btn-danger btn-sm rounded-4">Remove All Shifts on Calendar</button>
                        </div>

                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        <button type="button" class="btn btn-success btn-l rounded-4" id="applyShiftBtn">
                            Apply Shift Schedule
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade rounded-4" id="resetModal" tabindex="-1" aria-labelledby="resetModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetModalLabel">Confirm Reset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to remove all shifts on the calendar?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger rounded-4" id="confirmResetBtn">Yes, Reset</button>
                <button type="button" class="btn btn-secondary rounded-4" data-bs-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="setsched_employeeid" value="<?= $employee_id ?>">
<input type="hidden" id="setsched_department" value="<?= $department ?>">
<input type="hidden" id="setsched_area" value="<?= $area ?>">


<?php include '../includes/footer_upper.php'; ?>
<script src="../assets/js/functions.js"></script>
<script src="../assets/js/wtm/wtm_setched.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {

    });
</script>
<?php include '../includes/footer_lower.php'; ?>