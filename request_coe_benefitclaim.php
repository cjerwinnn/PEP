<?php
session_start();

if (!isset($_POST['employee_id'])) {
    echo "<p>No request selected.</p>";
    exit;
}

// Store BR number and include the fetch logic
$_POST['employee_id'] = $_POST['employee_id'];
include 'fetch/request_coe_employee.php';

$employee_id = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : '';
$emp_lastname = isset($_SESSION['lastname']) ? $_SESSION['lastname'] : '';
$emp_firstname = isset($_SESSION['firstname']) ? $_SESSION['firstname'] : '';
$emp_middlename = isset($_SESSION['middlename']) ? $_SESSION['middlename'] : '';
$emp_suffix = isset($_SESSION['suffix']) ? $_SESSION['suffix'] : '';
$emp_department = isset($_SESSION['department']) ? $_SESSION['department'] : '';
$emp_area = isset($_SESSION['area']) ? $_SESSION['area'] : '';
$emp_position = isset($_SESSION['position']) ? $_SESSION['position'] : '';

$emp_fullname = $_SESSION['firstname'] .
    (!empty($_SESSION['middlename']) ? ' ' . $_SESSION['middlename'] : '') .
    ' ' . $_SESSION['lastname'] .
    ($_SESSION['suffix'] !== 'NOT APPLICABLE' && !empty($_SESSION['suffix']) ? ' ' . $_SESSION['suffix'] : '');


?>


<div class="container-fluid mb-2">
    <div class="card shadow rounded-4">
        <div class="card-body p-4">
            <h5 class="card-title mb-4 text-muted">COE Requisition Form for BENEFIT CLAIM</span></h5>

            <div class="row mb-2">
                <div class="col-md-4 mb-4">
                    <label class="form-label small">Request ID</label>
                    <input type="text" class="form-control rounded-4 text-danger" id="req_id" value="" disabled>
                </div>

                <div class="col-md-8 mb-4">
                    <label class="form-label small">COE Type</label>
                    <input type="text" class="form-control rounded-4 text-danger" id="req_coe_type" value="BENEFIT CLAIM" disabled>
                </div>
            </div>

            <div class="d-flex align-items-center my-4">
                <hr class="flex-grow-1">
                <h5 class="mx-3 mb-0 text-muted small">Request Information</h5>
                <hr class="flex-grow-1">
            </div>

            <div class="row mb-2">
                <div class="col-md-6">
                    <label class="form-label small">Employee ID</label>
                    <input type="text" class="form-control rounded-4" id="emp_id" value="<?php echo htmlspecialchars($employee_id); ?>" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Employee Name</label>
                    <input type="text" class="form-control rounded-4" id="emp_name" value="<?php echo htmlspecialchars($emp_fullname); ?>" disabled>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-6">
                    <label class="form-label small">Department</label>
                    <input type="text" class="form-control rounded-4" id="emp_dept" value="<?php echo htmlspecialchars($emp_department); ?>" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Area/Section</label>
                    <input type="text" class="form-control rounded-4" id="emp_area" value="<?php echo htmlspecialchars($emp_area); ?>" disabled>
                </div>
            </div>

            <div class="mb-2">
                <label class="form-label small">Position</label>
                <input type="text" class="form-control rounded-4" id="emp_position" value="<?php echo htmlspecialchars($emp_position); ?>" disabled>
            </div>

            <div class="mb-2">
                <label for="req_reason" class="form-label small">
                    Requisition Reason<span class="text-danger"> *</span>
                </label>
                <textarea class="form-control rounded-4" id="req_reason" rows="2" placeholder="Describe the reason..."></textarea>
            </div>

            <div class="col-12 col-md-3 mb-2">
                <label for="date_needed" class="form-label small">Date Needed<span class="text-danger"> *</span></label>
                <input type="date" class="form-control rounded-4" id="date_needed">
            </div>

            <div class="col-12 col-md-3 mb-4">
                <label for="receivingFormat" class="form-label small">Receiving Format<span class="text-danger"> *</span></label>
                <select class="form-select rounded-4" id="receivingFormat" name="receivingFormat" required>
                    <option value="" disabled selected>Select an option</option>
                    <option value="SOFT COPY">SOFT COPY</option>
                    <option value="HARD COPY">HARD COPY</option>
                </select>
            </div>

            <div class="d-flex align-items-center my-4">
                <hr class="flex-grow-1">
                <h5 class="mx-3 mb-0 text-muted small">Claim Information</h5>
                <hr class="flex-grow-1">
            </div>

            <div class="col-12 col-md-3 mb-4">
                <label for="Claim_Type" class="form-label small">Claim Type<span class="text-danger"> *</span></label>
                <select class="form-select rounded-4" id="Claim_Type" name="Claim_Type" required>
                    <option value="" disabled selected>Select an option</option>
                    <option value="SSS">SSS</option>
                    <option value="PAG-IBIG">PAG-IBIG</option>
                    <option value="PHILHEALTH">PHILHEALTH</option>
                </select>
            </div>

            <div class="col-12 col-md-3 mb-4">
                <input class="form-check-input me-2" type="checkbox" value="" id="ck_compensation">
                <label class="form-check-label" for="ck_compensation">
                    with Compensation?
                </label>
            </div>


            <div class="col-md-12 ms-auto" id="attachment-section">
                <div class="card shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Supporting Documents</h5>
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

            <div class="text-end mt-4">
                <button type="button" id="submit_btn" class="btn btn-primary px-4 rounded-4">Submit Request</button>
            </div>
        </div>
    </div>
</div>

<!-- MODALS -->

<!-- Submit -->
<div class="modal fade" id="SubmitModal" tabindex="-1" aria-labelledby="SubmitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header">
                <h5 class="modal-title" id="SubmitModalLabel">COE Request for Benefit Claim</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="summary-content">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger rounded-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="submit_request" class="btn btn-primary rounded-4" data-bs-dismiss="modal">Confirm</button>
            </div>
        </div>
    </div>
</div>