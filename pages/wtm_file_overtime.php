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
$timein      = $_POST['timein'] ?? '';
$timeout     = $_POST['timeout'] ?? '';
$excess    = $_POST['overtime'] ?? '';
?>

<input type="hidden" id="shiftdate_data" value="<?= $shiftdate ?>">
<input type="hidden" id="department_data" value="ADMIN SERVICES">
<input type="hidden" id="area_data" value="IT">

<div class="container-fluid mb-2">
    <div class="card shadow rounded-4">
        <div class="card-body p-4">

            <div class="d-flex align-items-center mb-3">
                <a href="wtm_mydtr.php"
                    class="btn btn-outline-secondary btn-sm rounded-4">
                    ← Back
                </a>
                <h5 class="mb-0 ms-3 text-muted text-center">Overtime Application</h5>
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

                            <div class="text-muted mb-2">Shift Schedule: <span id="ot-shiftschedule" class="fw-semibold mt-3"><?= $shiftDisplay ?></span></div>

                            <div class="text-muted">Time In: <span id="ot-time-in" class="fw-semibold mt-3"><?= substr($timein, 0, 5) ?></span></div>
                            <div class="text-muted">Time Out: <span id="ot-time-out" class="fw-semibold mt-3"><?= substr($timeout, 0, 5) ?></span></div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-2">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-light h-100">

                            <div class="text-muted">
                                Excess: <span id="ot-excess" class="fw-semibold mt-3" value="<?= $excess ?>"><?= $excess ?></span>
                            </div>
                            <div class="text-muted">
                                File Overtime:
                                <input type="number"
                                    max=<?= $excess ?>
                                    min="1.0"
                                    value=<?= $excess ?>
                                    step="0.5"
                                    id="ot-file"
                                    class="text-center fw-bold form-control form-control-sm d-inline-block w-auto mt-1 rounded-4">
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
                    Submit application
                </button>
            </div>

        </div>
    </div>
</div>

<?php include '../includes/footer_upper.php'; ?>
<script src="../assets/js/wtm/wtm_overtime.js"></script>
<?php include '../includes/footer_lower.php'; ?>