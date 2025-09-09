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

$shiftdate        = $_POST['date'] ?? '';
$dateObj = new DateTime($shiftdate);
$formattedDate = $dateObj->format('M d, Y D');
$shiftDisplay   = $_POST['shiftDisplay'] ?? '';
$shiftcode   = $_POST['shiftcode'] ?? '';
$shiftstart  = $_POST['shiftstart'] ?? '';
$shiftend    = $_POST['shiftend'] ?? '';
$datein      = $_POST['datein'] ?? '';
$timein      = $_POST['timein'] ?? '';
$dateout      = $_POST['dateout'] ?? '';
$timeout     = $_POST['timeout'] ?? '';
$excess    = $_POST['overtime'] ?? '';

$shiftdate_plus1 = date('Y-m-d', strtotime($shiftdate . ' +1 day'));
?>

<?php

if ($conn2->connect_error) {
    die("DB connection failed: " . $conn2->connect_error);
}

$stmt = $conn2->prepare("CALL WEB_GET_EMP_PAYROLLTYPE(?)");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $employee_payrolltype = $row['payrolltype'];
} else {
    $employee_payrolltype = "DAILY";
}

$stmt->close();
$conn2->close();
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<input type="hidden" id="shiftdate_data" value="<?= $shiftdate ?>">
<input type="hidden" id="shiftcode_data" value="<?= $shiftcode ?>">
<input type="hidden" id="shiftin_data" value="<?= $shiftstart ?>">
<input type="hidden" id="shiftout_data" value="<?= $shiftend ?>">
<input type="hidden" id="employeename_data" value="<?= $employeename ?>">
<input type="hidden" id="department_data" value="<?= $department ?>">
<input type="hidden" id="area_data" value="<?= $area ?>">
<input type="hidden" id="position_data" value="<?= $position ?>">
<input type="hidden" id="employeepayrolltype_data" value="<?= $employee_payrolltype ?>">

<div class="container-fluid mb-2">
    <div class="card shadow rounded-4">
        <div class="card-body p-4">

            <div class="d-flex align-items-center mb-3">
                <a href="wtm_mydtr.php"
                    class="btn btn-outline-secondary btn-sm rounded-4">
                    ← Back
                </a>
                <h5 class="mb-0 ms-3 text-muted text-center">Manual In/Out Request</h5>
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
                </div>
            </div>

            <!-- Attendance Section -->
            <div class="mb-2">
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <!-- Date & Shift -->
                            <div class="d-flex justify-content-center align-items-center mb-3">
                                <div id="ot-date" class="fw-semibold text-dark d-flex align-items-center">
                                    <i class="fs-4 bi bi-calendar3 me-2 text-primary text-center"></i>
                                    <span id="ot-date-value" class="fs-4"><?= $formattedDate ?></span>
                                </div>

                            </div>

                            <div class="text-muted text-center mb-2 fs-5">Shift Schedule: <span id="ot-shiftschedule" class="fw-semibold mt-3"><?= $shiftDisplay ?></span></div>


                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-2">
                <!-- RECORDED TIME IN -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header text-center fw-bold">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Recorded Time In
                        </div>
                        <div class="card-body bg-light">
                            <div class="row g-2">
                                <!-- Date In -->
                                <div class="col-md-6">
                                    <label for="attendance_date_in" class="form-label cursor-default text-muted">Date In</label>
                                    <?php if ($datein === null || $datein === '' || $datein === '00:00:00'): ?>
                                        <div class="form-control form-control-sm text-center rounded-4 fw-bold">
                                            <span class="text-danger">No Date In</span>
                                        </div>
                                    <?php else: ?>
                                        <input type="date"
                                            id="attendance-date-in"
                                            name="attendance_date_in"
                                            value="<?= $datein ?>"
                                            class="form-control form-control-sm text-center rounded-4 fw-bold"
                                            disabled readonly>
                                    <?php endif; ?>
                                </div>

                                <!-- Time In -->
                                <div class="col-md-6">
                                    <label for="attendance-time-in" class="form-label cursor-default text-muted">Time In</label>
                                    <?php if ($timein === null || $timein === '' || $timein === '00:00:00'): ?>
                                        <div class="form-control form-control-sm text-center rounded-4 fw-bold">
                                            <span class="text-danger">No Time In</span>
                                        </div>
                                    <?php else: ?>
                                        <input type="time"
                                            id="attendance-time-in"
                                            name="attendance_time_in"
                                            value="<?= $timein ?>"
                                            class="form-control form-control-sm text-center rounded-4 fw-bold"
                                            disabled readonly>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RECORDED TIME OUT -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header text-center fw-bold">
                            <i class="bi bi-box-arrow-left me-1"></i> Recorded Time Out
                        </div>
                        <div class="card-body bg-light">
                            <div class="row g-2">
                                <!-- Date Out -->
                                <div class="col-md-6">
                                    <label class="form-label cursor-default text-muted">Date Out</label>
                                    <?php if ($dateout === null || $dateout === '' || $dateout === '00:00:00'): ?>
                                        <div class="form-control form-control-sm text-center rounded-4 text-danger fw-bold">
                                            <span class="text-danger">No Date Out</span>
                                        </div>
                                    <?php else: ?>
                                        <input type="date"
                                            value="<?= $dateout ?>"
                                            class="form-control form-control-sm text-center rounded-4 text-danger fw-bold"
                                            disabled readonly>
                                    <?php endif; ?>
                                </div>

                                <!-- Time Out -->
                                <div class="col-md-6">
                                    <label for="attendance-time-out" class="form-label cursor-default text-muted">Time Out</label>
                                    <?php if ($timeout === null || $timeout === '' || $timeout === '00:00:00'): ?>
                                        <div class="form-control form-control-sm text-center rounded-4 fw-bold">
                                            <span class="text-danger">No Time Out</span>
                                        </div>
                                    <?php else: ?>
                                        <input type="time"
                                            id="attendance-time-out"
                                            name="attendance_time_out"
                                            value="<?= $timeout ?>"
                                            class="form-control form-control-sm text-center rounded-4 fw-bold"
                                            disabled readonly>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <div class="row g-3">
                    <!-- REQUEST MANUAL IN -->
                    <div class="col-md-6">
                        <div class="card border-primary h-100">
                            <div class="card-header bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-pencil-square me-1"></i> Request Manual In</span>
                                <div class="form-check m-0">
                                    <?php if ($timein !== null && $timein !== '' && $timein !== '00:00:00'): ?>
                                        <input class="form-check-input" type="checkbox" id="enable-time-in" name="enable_time_in">
                                        <label class="form-check-label small" for="enable_time_in">
                                            Edit Current Time In
                                        </label>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label">Select Date In <span class="text-danger">*</span></label>
                                        <input type="date"
                                            id="schedule-date-in"
                                            name="schedule_date_in"

                                            <?php if ($datein === null || $datein === '') {
                                                echo 'value=""';
                                            } else {
                                                echo 'value="' . $datein . '"';
                                            }
                                            ?>

                                            max="<?= $shiftdate ?>"
                                            min="<?= $shiftdate ?>"
                                            class="form-control form-control-sm text-center rounded-4 border-primary text-primary fw-bold"
                                            <?php if ($timein !== null && $timein !== '' && $timein !== '00:00:00') echo 'disabled'; ?>>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Select Time In <span class="text-danger">*</span></label>
                                        <input type="time"
                                            id="schedule-time-in"
                                            name="schedule_time_in"

                                            <?php if ($timein === null || $timein === '' || $timein === '00:00:00') {
                                                echo 'value=""';
                                            } else {
                                                echo 'value="' . $timein . '"';
                                            }
                                            ?>

                                            class="form-control form-control-sm text-center rounded-4 border-primary text-primary fw-bold"
                                            <?php if ($timein !== null && $timein !== '' && $timein !== '00:00:00') echo 'disabled'; ?>>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- REQUEST MANUAL OUT -->
                    <div class="col-md-6">
                        <div class="card border-danger h-100">
                            <div class="card-header bg-danger-subtle text-danger fw-bold d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-pencil-square me-1"></i> Request Manual Out</span>
                                <div class="form-check m-0">
                                    <?php if ($timeout !== null && $timeout !== '' && $timeout !== '00:00:00'): ?>
                                        <input class="form-check-input" type="checkbox" id="enable-time-out" name="enable_time_out">
                                        <label class="form-check-label small" for="enable_time_out">
                                            Edit Current Time Out
                                        </label>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label">Select Date Out <span class="text-danger">*</span></label>
                                        <input type="date"
                                            id="schedule-date-out"
                                            name="schedule_date_out"

                                            <?php if ($dateout === null || $dateout === '') {
                                                echo 'value=""';
                                            } else {
                                                echo 'value="' . $dateout . '"';
                                            }
                                            ?>

                                            max="<?= $shiftdate_plus1 ?>"
                                            min="<?= $shiftdate ?>"
                                            class="form-control form-control-sm text-center rounded-4 border-danger text-danger fw-bold"
                                            <?php if ($timeout !== null && $timeout !== '' && $timeout !== '00:00:00') echo 'disabled'; ?>>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Select Time Out <span class="text-danger">*</span></label>
                                        <input type="time"
                                            id="schedule-time-out"
                                            name="schedule_time_out"

                                            <?php if ($timeout === null || $timeout === '' || $timeout === '00:00:00') {
                                                echo 'value=""';
                                            } else {
                                                echo 'value="' . $timeout . '"';
                                            }
                                            ?>

                                            class="form-control form-control-sm text-center rounded-4 border-danger text-danger fw-bold"
                                            <?php if ($timeout !== null && $timeout !== '' && $timeout !== '00:00:00') echo 'disabled'; ?>>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="mb-2">
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">DTR</h6>
                                <div class="row g-3">
                                    <div class="col-md-4 col-lg-3">
                                        <div class="p-3 rounded-3 border bg-light text-center">
                                            <div class="small text-muted">Tardiness</div>
                                            <div id="modal-tardiness" class="fw-semibold mt-1">-</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-3">
                                        <div class="p-3 rounded-3 border bg-light text-center">
                                            <div class="small text-muted">Undertime</div>
                                            <div id="modal-undertime" class="fw-semibold mt-1">-</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-3">
                                        <div class="p-3 rounded-3 border bg-light text-center">
                                            <div class="small text-muted">Night Diff</div>
                                            <div id="modal-nightdiff" class="fw-semibold mt-1">-</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-3">
                                        <div class="p-3 rounded-3 border bg-light text-center">
                                            <div class="small text-muted">Excess</div>
                                            <div id="modal-overtime" class="fw-semibold mt-1">-</div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 col-lg-6">
                                        <div class="p-3 rounded-3 border bg-light text-center">
                                            <div class="small text-muted">Total Man Hours</div>
                                            <div id="modal-manhrs" class="fw-semibold mt-1">-</div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 col-lg-6">
                                        <div class="p-3 rounded-3 border bg-light text-center">
                                            <div class="small text-muted">Transaction Count</div>
                                            <div id="modal-trancount" class="fw-semibold mt-1">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Remarks -->
                            <div>
                                <h6 class="fw-bold mb-2">Remarks</h6>
                                <div id="modal-remarks" class="p-3 rounded-3 border bg-light text-muted"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-2">
                <div class="mb-2">
                    <label for="req_reason" class="form-label small">
                        Justification <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control rounded-4" id="req_reason" rows="2"
                        placeholder="Overtime justification..."
                        oninput="adjustTextareaHeight(this)"></textarea>
                </div>
            </div>

            <div class="d-flex justify-content-center mb-2">
                <button type="button" class="btn btn-success btn-l rounded-4 mt-4" id="SubmitChangeShift_Btn">
                    Submit request
                </button>
            </div>

        </div>
    </div>
</div>

<?php include '../includes/footer_upper.php'; ?>
<script src="../assets/js/wtm/wtm_computation.js"></script>
<script src="../assets/js/wtm/wtm_manualattendance_request.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr("#attendance-time-in", {
        enableTime: false,
        noCalendar: true,
        time_24hr: true,
        dateFormat: "H:i",
        minuteIncrement: 1
    });
    flatpickr("#attendance-time-out", {
        enableTime: false,
        noCalendar: true,
        time_24hr: true,
        dateFormat: "H:i",
        minuteIncrement: 1
    });
    flatpickr("#schedule-time-in", {
        enableTime: true,
        noCalendar: true,
        time_24hr: true,
        dateFormat: "H:i",
        minuteIncrement: 1
    });
    flatpickr("#schedule-time-out", {
        enableTime: true,
        noCalendar: true,
        time_24hr: true,
        dateFormat: "H:i",
        minuteIncrement: 1
    });
</script>
<?php include '../includes/footer_lower.php'; ?>