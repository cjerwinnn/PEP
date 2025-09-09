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

$shiftdate = isset($_SESSION['shiftdate']) ? $_SESSION['shiftdate'] : '';

$shiftdate_obj = new DateTime($shiftdate);
$shiftdate_formatted = $shiftdate_obj->format('M d, Y (D)');

$shiftcode = isset($_SESSION['shiftcode']) ? $_SESSION['shiftcode'] : '';
$shiftstart = isset($_SESSION['shiftstart']) ? $_SESSION['shiftstart'] : '';
$shiftend = isset($_SESSION['shiftend']) ? $_SESSION['shiftend'] : '';

$datein = isset($_SESSION['datein']) ? $_SESSION['datein'] : '';
$timein = isset($_SESSION['timein']) ? $_SESSION['timein'] : '';
$dateout = isset($_SESSION['dateout']) ? $_SESSION['dateout'] : '';
$timeout = isset($_SESSION['timeout']) ? $_SESSION['timeout'] : '';

if ($shiftstart == '' && $shiftend == '') {
    $shiftDisplay = '[' . $shiftcode . ']';
} else {
    $shiftDisplay = '[' . $shiftcode . '] ' . $shiftstart . '-' . $shiftend;
}

$ScheduleList = isset($_SESSION['ScheduleList']) ? $_SESSION['ScheduleList'] : [];

?>

<?php
$stmt = $conn2->prepare("CALL WEB_OT_TYPE_LIST");
$stmt->execute();
$result = $stmt->get_result();

$ot_types = [];
while ($row = $result->fetch_assoc()) {
    $ot_types[] = $row;
}
?>

<input type="hidden" id="shiftdate_data" value="<?= $shiftdate ?>">

<div class="container-fluid mb-2">
    <div class="card shadow rounded-4">
        <div class="card-body p-4">

            <div class="d-flex align-items-center mb-3">
                <a href="wtm_mydtr.php"
                    class="btn btn-outline-secondary btn-sm rounded-4">
                    ← Back
                </a>
                <h5 class="mb-0 ms-3 text-muted text-center">Change Shift Schedule Request</h5>
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
                                    <span id="ot-date-value" class="fs-4"><?= $shiftdate_formatted ?></span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-2">
                <!-- RECORDED TIME IN -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header text-center fw-bold">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Attendance In
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
                            <i class="bi bi-box-arrow-left me-1"></i> Attendance Out
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

            <!-- Attendance Section -->
            <div class="mb-2">
                <h6 class="text-muted mb-2 small">Schedule</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <div class="small text-muted">Current Schedule</div>
                            <div id="modal-in" class="fw-semibold mt-3 fs-4 text-center"><?= $shiftDisplay ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <div class="small text-muted">Change Schedule To<span class="text-danger"> *</span></div>
                            <select class="form-select rounded-4 mt-2" id="changeschedto_dropdown">
                                <option value="" selected disabled>Select a schedule...</option>
                                <?php
                                if (!empty($_SESSION['ScheduleList'])) {
                                    foreach ($_SESSION['ScheduleList'] as $row) {
                                        // Assuming WEB_SC_SCHEDULE_LIST returns `shiftcode`, `shiftstart`, `shiftend`
                                        $shiftcode = $row['shiftcode'];
                                        $shiftstart = $row['shiftstart'];
                                        $shiftend = $row['shiftend'];
                                        $shiftdescription = $row['shiftdescription'];

                                        if ($shiftstart === '' && $shiftend === '') {
                                            echo '<option value="' . htmlspecialchars($shiftcode) . '">[' . htmlspecialchars($shiftcode) . '] ' . htmlspecialchars($shiftdescription) . '</option>';
                                        } else {
                                            echo '<option value="' . htmlspecialchars($shiftcode) . '">[' . htmlspecialchars($shiftcode) . '] ' . htmlspecialchars($shiftstart) . ' - ' . htmlspecialchars($shiftend) . '</option>';
                                        }
                                    }
                                }
                                ?>
                            </select>

                            <div id="modal-out" class="fw-semibold mt-1"></div>
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
                    Submit application
                </button>
            </div>

        </div>
    </div>
</div>

<?php include '../includes/footer_upper.php'; ?>
<script src="../assets/js/wtm/wtm_computation.js"></script>
<script src="../assets/js/wtm/wtm_changeshift_filing.js"></script>
<?php include '../includes/footer_lower.php'; ?>