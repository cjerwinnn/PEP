<?php
include '../config/connection.php';
include '../includes/header.php';

// Employee picture
$defaultImage = '../../assets/imgs/user_default.png';
$form_picture = isset($_SESSION['form_picture']) ? $_SESSION['form_picture'] : '';

if (!empty($form_picture)) {
    $form_picture = 'data:image/jpeg;base64,' . base64_encode($form_picture);
} else {
    $form_picture = $defaultImage;
}

// Employee info
$employee_id   = $_SESSION['employee_id'] ?? '';
$employeename  = $_SESSION['employeename'] ?? '';
$department    = $_SESSION['department'] ?? '';
$area          = $_SESSION['area'] ?? '';
$position      = $_SESSION['position'] ?? '';
$lastname      = $_SESSION['lastname'] ?? '';
$firstname     = $_SESSION['firstname'] ?? '';
$middlename    = $_SESSION['middlename'] ?? '';
$suffix        = $_SESSION['suffix'] ?? '';

// Attachments
$attachmentData = $_SESSION['AttachmentsData'] ?? [];
$LogsData = isset($_SESSION['LogsData']) ? $_SESSION['LogsData'] : [];

// Overtime info
$overtime_id   = $_SESSION['overtimeid'] ?? '';
$overtimedate = $_SESSION['overtimedate'] ?? '';

$formattedOvertimeDate = '';
if (!empty($overtimedate)) {
    $dateObj = new DateTime($overtimedate);
    $formattedOvertimeDate = $dateObj->format('M d, Y'); // MMM dd, yyyy
}

$totalovertime = $_SESSION['totalovertime'] ?? '';
$overtimetype = $_SESSION['overtimetype'] ?? '';
$reason        = $_SESSION['reason'] ?? '';
$excess_hours  = $_SESSION['excess_hours'] ?? '';
$status        = $_SESSION['status'] ?? '';

// Shift info
$shiftcode    = $_SESSION['shiftcode'] ?? '';
$shiftstart   = $_SESSION['shiftstart'] ?? '';
$shiftend     = $_SESSION['shiftend'] ?? '';
$shiftdate    = $_SESSION['shiftdate'] ?? '';
$shiftDisplay = $_SESSION['shiftDisplay'] ?? '';

// Format shift date
$formattedDate = '';
if (!empty($shiftdate)) {
    $dateObj = new DateTime($shiftdate);
    $formattedDate = $dateObj->format('M d, Y D');
}

// Time info
$datein   = $_SESSION['datein'] ?? '';
$dateout  = $_SESSION['dateout'] ?? '';
$timein   = $_SESSION['timein'] ?? '';
$timeout  = $_SESSION['timeout'] ?? '';

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

<input type="hidden" id="overtimeid_data" value="<?= $overtime_id ?>">
<input type="hidden" id="requestorempid_data" value="<?= $employee_id ?>">
<input type="hidden" id="shiftdate_data" value="<?= $overtimedate ?>">
<input type="hidden" id="department_data" value="<?= $department ?>">
<input type="hidden" id="area_data" value="<?= $area ?>">

<div class="container-fluid mb-2">
    <div class="card shadow rounded-4">
        <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center mb-3">
                    <a href="wtm_mydtr.php" class="btn btn-outline-secondary btn-sm rounded-4">
                        ← Back
                    </a>
                    <h5 class="mb-0 ms-3 text-muted text-center">Overtime Application</h5>
                </div>

                <div>
                    Overtime ID: <span class="me-2 fw-bold text-primary"><?= $overtime_id ?></span>
                    <span class="badge bg-success fs-8"><?= $status ?></span>
                </div>
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
                                    <span id="ot-date-value" class="fs-4"><?= $formattedOvertimeDate ?></span>
                                </div>
                            </div>
                            <div class="text-muted text-center mb-2 fs-5">Shift Schedule: <span id="ot-shiftschedule" class="fw-semibold mt-3">[<?= $shiftcode ?>] <?= $shiftstart ?>-<?= $shiftend ?></span></div>
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

            <div class="mb-2">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-light h-100">

                            <div class="text-muted">
                                Excess: <span id="ot-excess" class="fw-semibold mt-3" value="<?= $excess_hours ?>"><?= $excess_hours ?></span>
                            </div>

                            <div class="text-muted">
                                Filed Overtime: <span id="ot-file" class="fw-semibold mt-3" value="<?= $excess_hours ?>"><?= $totalovertime ?></span>
                            </div>

                            <div class="text-muted d-none">
                                Approved Overtime:
                                <input type="number"
                                    max=<?= $totalovertime ?>
                                    min="1.0"
                                    value=""
                                    step="0.5"
                                    id="ot-approved-view"
                                    class="text-center fw-bold form-control form-control-sm d-inline-block w-auto mt-1 rounded-4">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-light h-100">
                            <div class="small text-muted">Overtime Type<span class="text-danger"> *</span></div>
                            <select class="form-select rounded-4 mt-2" id="overtimetype_dropdown" name="overtimetype" disabled>
                                <option value="" disabled <?= empty($overtimetype) ? 'selected' : '' ?>>Select a type...</option>
                                <?php foreach ($ot_types as $ot) : $optionValue = $ot['ottype'] . ' - ' . $ot['description']; ?>
                                    <option value="<?= htmlspecialchars($optionValue) ?>"
                                        <?= (isset($overtimetype) && $overtimetype === $optionValue) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($optionValue) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-2">
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="card shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0 text-center">Supporting Documents</h5>
                            </div>
                            <div class="card-body">
                                <form action="upload.php" method="post" enctype="multipart/form-data">

                                    <div class="mb-3">
                                        <div id="attachment-list" style="display: block;">
                                            <table id="fileList" class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">File Name</th>
                                                        <th scope="col">File Size</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- List of selected files will appear here -->
                                                    <?php
                                                    function formatFileSizeDisplay($size)
                                                    {
                                                        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                                                        $i = 0;

                                                        while ($size >= 1024 && $i < count($units) - 1) {
                                                            $size /= 1024;
                                                            $i++;
                                                        }

                                                        return number_format($size, 2) . ' ' . $units[$i];
                                                    }
                                                    ?>

                                                    <?php if (!empty($attachmentData)): ?>
                                                        <?php foreach ($attachmentData as $attachment): ?>
                                                            <?php
                                                            $stored = htmlspecialchars($attachment['stored_filename']);
                                                            $original = htmlspecialchars($attachment['original_filename']);
                                                            ?>
                                                            <tr class="server-file-row">
                                                                <td class="text-start">
                                                                    <a href="view-file.php?file=<?php echo urlencode($stored); ?>" target="_blank" class="text-decoration-none">
                                                                        <?php echo $original; ?>
                                                                    </a>
                                                                </td>
                                                                <td class="text-start file-size">
                                                                    <?php echo htmlspecialchars(formatFileSizeDisplay($attachment['file_size'])); ?>
                                                                </td>
                                                            </tr>


                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="3" class="text-center">No Attachments found.</td>
                                                        </tr>
                                                    <?php endif; ?>

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </form>
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
                        oninput="adjustTextareaHeight(this)" disabled readonly><?= $reason ?></textarea>
                </div>
            </div>

            <?php if ($status === 'PENDING'): ?>
                <div class="d-flex justify-content-center gap-3 mb-2">
                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-danger px-4 rounded-4 mb-2" data-bs-toggle="modal" data-bs-target="#CancelModal">Cancel Application</button>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<div class="container-fluid mb-2">
    <div class="col-md-12 ms-auto mt-2" id="trail-section">
        <div class="card shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">Request Logs</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle rounded-4 overflow-hidden shadow-sm">
                        <thead class="table text-center bg-muted">
                            <tr>
                                <th scope="col">Approved Hour(s)</th>
                                <th scope="col">Action Remarks</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action Taken By</th>
                                <th scope="col">Date</th>
                                <th scope="col">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($LogsData)): ?>
                                <?php foreach ($LogsData as $Logs): ?>
                                    <tr>
                                        <td class="text-center fw-bold"><?= htmlspecialchars($Logs['approved_hours']) ?></td>
                                        <td><?= htmlspecialchars($Logs['action_remarks']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($Logs['request_status']) ?></td>
                                        <td><?= htmlspecialchars($Logs['action_by']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($Logs['action_date']) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($Logs['action_time']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No Attachments found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="CancelModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="true" aria-labelledby="CancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-m modal-dialog-centered rounded-4">
        <div class="modal-content rounded-4">
            <div class="modal-header py-2 bg-danger text-white">
                <h5 class="modal-title">Cancel Application</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" class="rounded-4 border-1 border-danger">
                    <div class="mb-2 mt-2">
                        <textarea class="form-control rounded-4" id="cancel_remarks" rows="2" placeholder="Remarks..." oninput="adjustTextareaHeight(this)"></textarea>
                    </div>
                    <div class="text-end mt-2">
                        <button type="button" id="cancel_request" class="btn btn-outline-danger rounded-4">Cancel application</button>
                        <button type="button" id="cancel_no_request" class="btn btn-danger rounded-4">No</button>
                    </div>
                </div>

                <div id="waiting-approval-access" class="text-danger">
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer_upper.php'; ?>
<script src="../assets/js/wtm/wtm_overtime_viewonly.js"></script>
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
</script>
<?php include '../includes/footer_lower.php'; ?>