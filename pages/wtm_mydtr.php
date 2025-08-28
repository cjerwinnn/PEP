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

<input hidden type="hidden" id="emp-area" value="<?= $area ?>">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container-fluid mb-2">
    <div class="card shadow rounded-4">
        <div class="card-body p-4">

            <div class="d-flex align-items-center mb-3">
                <a href="wtm_submenu.php" class="btn btn-outline-secondary btn-sm rounded-4">
                    ← Back
                </a>
                <h5 class="mb-0 ms-3 text-muted text-center">My Daily Time Record</h5>
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

            <div class="row mb-4">
                <div class="col-12 col-md-3 d-flex align-items-center gap-2">
                    <select id="cutoffDropdown" class="form-select rounded-4" onchange="cutoffChanged()">
                    </select>
                </div>
            </div>

            <div id="attendance-container" class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle text-center" id="attendanceTable" style="min-width: 1000px;">
                    <thead class="table-light text-dark border-0">
                        <tr>
                            <th rowspan="2" class="align-middle">Date</th>
                            <th colspan="4">Schedule</th>
                            <th colspan="4">Attendance</th>
                            <th colspan="6">DTR</th>
                            <th rowspan="2" class="align-middle">Remarks</th>
                        </tr>
                        <tr>
                            <th>Day</th>
                            <th>Shift Code</th>
                            <th>Shift Start</th>
                            <th>Shift End</th>
                            <th>Date In</th>
                            <th>Time In</th>
                            <th>Date Out</th>
                            <th>Time Out</th>
                            <th>Tardiness (m)</th>
                            <th>Undertime (m)</th>
                            <th>Man Hours (H)</th>
                            <th>Night Diff (H)</th>
                            <th>Excess (H)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="fw-normal text-secondary">
                        <tr>
                            <td colspan="16" class="text-center py-4 text-muted">
                                <em><i class="bi bi-info-circle me-2"></i>Select an Cut-off to view attendance</em>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="modal fade" id="DTRDetailModal" tabindex="-1" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content rounded-4 shadow border-0">

                        <!-- Header -->
                        <div class="modal-header bg-light border-0 rounded-top-4">
                            <h5 class="mb-0 fw-bold">DTR Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <!-- Body -->
                        <div class="modal-body p-4">

                            <!-- Date & Shift -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div id="modal-date" class="fw-semibold text-dark d-flex align-items-center me-2">
                                    <i class="bi bi-calendar3 me-2 text-primary"></i>
                                    <span id="modal-date-value" class="fs-6"></span>
                                </div>

                                <!-- Shift -->
                                <div id="modal-shift"
                                    class="px-3 py-2 rounded-pill fw-semibold bg-muted text-dark shadow-sm small">
                                </div>
                            </div>


                            <!-- Attendance Section -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">Attendance</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="p-3 rounded-3 border bg-light h-100">
                                            <div class="small text-muted">Time In</div>
                                            <div id="modal-in" class="fw-semibold mt-1"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded-3 border bg-light h-100">
                                            <div class="small text-muted">Time Out</div>
                                            <div id="modal-out" class="fw-semibold mt-1"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Performance Section -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">DTR</h6>
                                <div class="row g-3">
                                    <div class="col-md-6 col-lg-3">
                                        <div class="p-3 rounded-3 border bg-light text-center">
                                            <div class="small text-muted">Tardiness</div>
                                            <div id="modal-tardiness" class="fw-semibold mt-1"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <div class="p-3 rounded-3 border bg-light text-center">
                                            <div class="small text-muted">Undertime</div>
                                            <div id="modal-undertime" class="fw-semibold mt-1"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <div class="p-3 rounded-3 border bg-light text-center">
                                            <div class="small text-muted">Night Diff</div>
                                            <div id="modal-nightdiff" class="fw-semibold mt-1"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <div class="p-3 rounded-3 border bg-light text-center">
                                            <div class="small text-muted">Excess</div>
                                            <div id="modal-overtime" class="fw-semibold mt-1"></div>
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


            <div class="modal fade" id="ChangeShiftModal" tabindex="-1" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content rounded-4 shadow border-0">

                        <!-- Header -->
                        <div class="modal-header bg-light border-0 rounded-top-4 d-flex align-items-center justify-content-between">

                            <div class="d-flex align-items-center">
                                <button class="btn btn-outline-secondary btn-sm rounded-4 me-3" data-bs-toggle="modal" data-bs-target="#DTRDetailModal">
                                    ← Back
                                </button>
                                <h5 class="mb-0 fw-bold">Change Shift Schedule</h5>
                                <input hidden type="text" id="hidden_date_selected">
                            </div>

                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <!-- Body -->
                        <div class="modal-body p-4">

                            <!-- Date & Shift -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div id="modal-date" class="fw-semibold text-dark d-flex align-items-center me-2">
                                    <i class="bi bi-calendar3 me-2 text-primary"></i>
                                    <span id="modal-date-value" class="fs-6"></span>
                                </div>
                            </div>


                            <!-- Attendance Section -->
                            <div class="mb-2">
                                <h6 class="fw-bold mb-2">Schedule</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="p-3 rounded-3 border bg-light h-100">
                                            <div class="small text-muted">Current Schedule</div>
                                            <div id="modal-in" class="fw-semibold mt-3"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded-3 border bg-light h-100">
                                            <div class="small text-muted">Change Schedule To<span class="text-danger"> *</span></div>
                                            <select class="form-select rounded-4 mt-2" id="changeschedto_dropdown">
                                                <option value="" selected disabled>Select a schedule...</option>
                                            </select>
                                            <div id="modal-out" class="fw-semibold mt-1"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-2">
                                <div class="mb-2">
                                    <label for="req_reason" class="form-label small">
                                        Reason <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control rounded-4" id="req_reason" rows="2"
                                        placeholder="Change shift schedule reason..."
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
            </div>


            <!-- FILE OVERTIME -->

            <div class="modal fade" id="OvertimeModal" tabindex="-1" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content rounded-4 shadow border-0">

                        <!-- Header -->
                        <div class="modal-header bg-light border-0 rounded-top-4 d-flex align-items-center justify-content-between">

                            <div class="d-flex align-items-center">
                                <button class="btn btn-outline-secondary btn-sm rounded-4 me-3" data-bs-toggle="modal" data-bs-target="#DTRDetailModal">
                                    ← Back
                                </button>
                                <h5 class="mb-0 fw-bold">File Overtime</h5>
                                <input hidden type="text" id="ot_hidden_date_selected">
                            </div>

                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <!-- Body -->
                        <div class="modal-body p-4">

                            <!-- Date & Shift -->
                            <div class="d-flex justify-content-center align-items-center mb-3">
                                <div id="modal-date" class="fw-semibold text-dark d-flex align-items-center">
                                    <i class="fs-4 bi bi-calendar3 me-2 text-primary text-center"></i>
                                    <span id="modal-date-value" class="fs-4"></span>
                                </div>
                            </div>

                            <!-- Attendance Section -->
                            <div class="mb-2">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="p-3 rounded-3 border bg-light h-100">
                                            <div class="text-muted mb-2">Shift Schedule: <span id="ot-shiftschedule" class="fw-semibold mt-3"></span></div>

                                            <div class="text-muted">Time In: <span id="ot-time-in" class="fw-semibold mt-3"></span></div>
                                            <div class="text-muted">Time Out: <span id="ot-time-out" class="fw-semibold mt-3"></span></div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-2">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="p-3 rounded-3 border bg-light h-100">

                                            <div class="text-muted">
                                                Excess: <span id="ot-excess" class="fw-semibold mt-3"></span>
                                            </div>
                                            <div class="text-muted">
                                                File Overtime:
                                                <input type="number" id="ot-file" class="text-center fw-bold form-control form-control-sm d-inline-block w-auto mt-1 rounded-4">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded-3 border bg-light h-100">
                                            <div class="small text-muted">Overtime Type<span class="text-danger"> *</span></div>
                                            <select class="form-select rounded-4 mt-2" id="overtimetype_dropdown">
                                                <option value="" selected disabled>Select a type...</option>
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
                                                    <div class="mb-2 text-center">
                                                        <input
                                                            type="file"
                                                            class="form-control"
                                                            name="files[]"
                                                            id="files"
                                                            multiple
                                                            required
                                                            style="display: none;"
                                                            accept=".pdf,.docx,.xlsx,.jpg,.jpeg,.png,.gif,.bmp,.tiff" />

                                                        <button
                                                            type="button"
                                                            class="btn btn-outline-secondary rounded-4"
                                                            id="btnBrowseFiles">
                                                            <i class="lni lni-upload-1 me-2"></i> Browse Files
                                                        </button>


                                                        <p class="form-text text-danger fst-italic mt-2">You can select multiple files to upload. Allowed formats: PDF, Word, Excel, and Images (JPG, PNG, GIF, etc.).</p>
                                                        <p class="form-text text-danger fst-italic fw-bold mb-0">Maximum of 10Mb per file.</p>
                                                    </div>

                                                    <div class="mb-3">
                                                        <div id="attachment-list" style="display: none;">
                                                            <table id="fileList" class="table table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th scope="col">File Name</th>
                                                                        <th scope="col">File Size</th>
                                                                        <th scope="col">Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <!-- List of selected files will appear here -->
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
            </div>




        </div>
    </div>
</div>

<?php include '../includes/footer_upper.php'; ?>
<script src="../assets/js/wtm/wtm_mydtr.js"></script>
<?php include '../includes/footer_lower.php'; ?>