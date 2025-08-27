   <?php
    include '../includes/header.php';

    $user_id = isset($_SESSION['employeeid']) ? $_SESSION['employeeid'] : '';

    if (!isset($_POST['employee_id'])) {
        echo "<p>No Employee selected.</p>";
        exit;
    }

    // Store BR number and include the fetch logic
    include '../fetch/request_coe_employee.php';

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

    if (!isset($_POST['request_id'])) {
        echo "<p>No request selected.</p>";
        exit;
    }

    // Store BR number and include the fetch logic
    include '../fetch/request_coe_fetch.php';

    // Now use $_SESSION variables to display content
    $request_id = isset($_SESSION['request_id']) ? $_SESSION['request_id'] : '';
    $employee_id = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : '';
    $coe_type = isset($_SESSION['coe_type']) ? $_SESSION['coe_type'] : '';
    $request_reason = isset($_SESSION['request_reason']) ? $_SESSION['request_reason'] : '';
    $date_needed = isset($_SESSION['date_needed']) ? $_SESSION['date_needed'] : '';
    $request_format = isset($_SESSION['request_format']) ? $_SESSION['request_format'] : '';
    $requested_date = isset($_SESSION['requested_date']) ? $_SESSION['requested_date'] : '';
    $requested_time = isset($_SESSION['requested_time']) ? $_SESSION['requested_time'] : '';
    $requested_by = isset($_SESSION['requested_by']) ? $_SESSION['requested_by'] : '';
    $request_status = isset($_SESSION['request_status']) ? $_SESSION['request_status'] : '';

    $attachmentData = isset($_SESSION['AttachmentsData']) ? $_SESSION['AttachmentsData'] : [];
    $LogsData = isset($_SESSION['LogsData']) ? $_SESSION['LogsData'] : [];

    if ($coe_type === 'TRAVEL') {
        $travel_datefrom = isset($_SESSION['travel_datefrom']) ? $_SESSION['travel_datefrom'] : '';
        $travel_dateto = isset($_SESSION['travel_dateto']) ? $_SESSION['travel_dateto'] : '';
        $date_return = isset($_SESSION['date_return']) ? $_SESSION['date_return'] : '';
        $travel_type = isset($_SESSION['travel_type']) ? $_SESSION['travel_type'] : '';
        $travel_location = isset($_SESSION['travel_location']) ? $_SESSION['travel_location'] : '';
    }
    ?>

   <div class="container-fluid mb-2">
       <div class="card shadow rounded-4">
           <div class="card-body p-4">
               <h5 class="card-title mb-4 text-muted">COE Requisition Form for Travel:
                   <td>
                       <?php
                        switch ($request_status) {
                            case 'PENDING':
                                $badgeClass = 'bg-dark';
                                break;
                            case 'APPROVED':
                                $badgeClass = 'bg-secondary';
                                break;
                            case 'ON PROCESS':
                                $badgeClass = 'bg-primary';
                                break;
                            case 'FOR SIGNING':
                                $badgeClass = 'bg-warning';
                                break;
                            case 'FOR RELEASING':
                                $badgeClass = 'bg-info';
                                break;
                            case 'RELEASED':
                                $badgeClass = 'bg-success';
                                break;
                            case 'DENIED':
                                $badgeClass = 'bg-danger';
                                break;
                            case 'DECLINED':
                                $badgeClass = 'bg-danger';
                                break;
                            default:
                                $badgeClass = 'bg-dark';
                        }

                        ?>

                       <a href="#trail-section" title="Click to see request logs" class="badge <?= $badgeClass ?> text-decoration-none">
                           <?= htmlspecialchars($request_status) ?>
                       </a>
                   </td>
               </h5>

               <input type="hidden" id="current_user_id" value="<?php echo htmlspecialchars(string: $user_id); ?>">

               <div class="row mb-2">
                   <div class="col-md-4 mb-4">
                       <label class="form-label small">Request ID</label>
                       <input type="text" class="form-control rounded-4 text-danger" id="req_id" value="<?php echo htmlspecialchars($request_id); ?>" disabled>
                   </div>

                   <div class="col-md-8 mb-4">
                       <label class="form-label small">COE Type</label>
                       <input type="text" class="form-control rounded-4 text-danger" id="req_coe_type" value="<?php echo htmlspecialchars($coe_type); ?>" disabled>
                   </div>
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
                   <textarea class="form-control rounded-4" id="req_reason" rows="2" placeholder="Describe the reason..." disabled><?php echo htmlspecialchars($request_reason); ?></textarea>
               </div>

               <div class="col-12 col-md-3 mb-2">
                   <label for="date_needed" class="form-label small">Date Needed<span class="text-danger"> *</span></label>
                   <input type="date" class="form-control rounded-4" id="date_needed" value="<?php echo htmlspecialchars($date_needed); ?>" disabled>
               </div>

               <div class="col-12 col-md-3 mb-4">
                   <label for="receivingFormat" class="form-label small">Receiving Format<span class="text-danger"> *</span></label>
                   <select class="form-select rounded-4" id="receivingFormat" name="receivingFormat" required disabled>
                       <option value="" disabled <?php echo empty($request_format) ? 'selected' : ''; ?>>Select an option</option>
                       <option value="SOFT COPY" <?php echo ($request_format == 'SOFT COPY') ? 'selected' : ''; ?>>SOFT COPY</option>
                       <option value="HARD COPY" <?php echo ($request_format == 'HARD COPY') ? 'selected' : ''; ?>>HARD COPY</option>
                   </select>
               </div>

               <div class="d-flex align-items-center my-4">
                   <hr class="flex-grow-1">
                   <h5 class="mx-3 mb-0 text-muted small">Travel Information</h5>
                   <hr class="flex-grow-1">
               </div>

               <div class="row">
                   <div class="col-12 col-md-4 mb-2">
                       <label for="dateFrom" class="form-label small">Travel Date From <span class="text-danger">*</span></label>
                       <input type="date" class="form-control rounded-4" id="dateFrom" name="dateFrom" value="<?php echo htmlspecialchars($travel_datefrom); ?>" disabled>
                   </div>

                   <div class="col-12 col-md-4 mb-2">
                       <label for="dateTo" class="form-label small">Date To <span class="text-danger">*</span></label>
                       <input type="date" class="form-control rounded-4" id="dateTo" name="dateTo" value="<?php echo htmlspecialchars($travel_dateto); ?>" disabled>
                   </div>
               </div>

               <div class="col-12 col-md-3 mb-2">
                   <label for="date_return" class="form-label small">Date Return to Work<span class="text-danger"> *</span></label>
                   <input type="date" class="form-control rounded-4" id="date_return" value="<?php echo htmlspecialchars($date_return); ?>" disabled>
               </div>

               <div class="col-12 col-md-3 mb-2">
                   <label for="receiving_Format" class="form-label small">Travel Type<span class="text-danger"> *</span></label>
                   <select class="form-select rounded-4" id="receiving_Format" name="receiving_Format" required disabled>
                       <option value="" disabled <?php echo empty($travel_type) ? 'selected' : ''; ?>>Select an option</option>
                       <option value="IN TOWN" <?php echo ($travel_type == 'IN TOWN') ? 'selected' : ''; ?>>IN TOWN</option>
                       <option value="OUT OF TOWN" <?php echo ($travel_type == 'OUT OF TOWN') ? 'selected' : ''; ?>>OUT OF TOWN</option>
                       <option value="OUT OF THE COUNTRY" <?php echo ($travel_type == 'OUT OF THE COUNTRY') ? 'selected' : ''; ?>>OUT OF THE COUNTRY</option>
                   </select>
               </div>

               <div class="mb-4">
                   <label class="form-label small">Location<span class="text-danger"> *</span></label>
                   <input type="text" class="form-control rounded-4" id="travel_location" placeholder="Eg. Boracay, Philippines or Tokyo, Japan" value="<?php echo htmlspecialchars($travel_location); ?>" disabled>
               </div>

               <div class="col-md-12 mt-3 mb-4" id="checklist-section">
                   <div class="card shadow-sm rounded-4 border-0">
                       <div class="card-header bg-danger text-white rounded-top-4">
                           <h5 class="mb-0 text-center"><i class="fas fa-list-check me-2"></i>Checklist Requirements</h5>
                       </div>
                       <div class="card-body p-0">
                           <div class="table-responsive rounded-4">
                               <table class="table table-hover align-middle mb-0" id="checklistTable">
                                   <thead class="table-light">
                                       <tr>
                                           <th>Requirement</th>
                                           <th>File</th>
                                           <th>File Size</th>
                                           <th>Status</th>
                                       </tr>
                                   </thead>
                                   <tbody id="dynamicChecklist">
                                       <!-- Checklist rows will be injected here -->
                                       <?php
                                        function ck_formatFileSizeDisplay($size)
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

                                       <?php if (!empty($checkListData)): ?>
                                           <?php foreach ($checkListData as $ck_attachment): ?>
                                               <?php
                                                $requirements_name = htmlspecialchars($ck_attachment['requirements_name']);
                                                $stored = htmlspecialchars($ck_attachment['stored_filename']);
                                                $original = htmlspecialchars($ck_attachment['original_filename']);
                                                $checklist_required = htmlspecialchars($ck_attachment['checklist_required']);
                                                ?>
                                               <tr class="server-file-row">
                                                   <td class="text-start">
                                                       <?php echo htmlspecialchars($requirements_name); ?>
                                                   </td>
                                                   <td class="text-start">
                                                       <?php if ($original === 'NO ATTACHMENT'): ?>
                                                           <span class="badge bg-danger">No Attach files</span>
                                                       <?php else: ?>
                                                           <a href="view-file.php?file=<?= urlencode($stored); ?>" target="_blank" class="text-decoration-none">
                                                               <?= htmlspecialchars($original); ?>
                                                           </a>
                                                       <?php endif; ?>
                                                   </td>

                                                   <td class="text-start file-size">
                                                       <?php echo htmlspecialchars(ck_formatFileSizeDisplay($ck_attachment['file_size'])); ?>
                                                   </td>
                                                   <td class="text-start file-size">
                                                       <?php if ($checklist_required == 1): ?>
                                                           <span class="badge bg-danger">Required</span>
                                                       <?php else: ?>
                                                           <span class="badge bg-secondary">Optional</span>
                                                       <?php endif; ?>
                                                   </td>
                                               </tr>

                                           <?php endforeach; ?>
                                       <?php else: ?>
                                           <tr>
                                               <td colspan="4" class="text-center">No Attachments found.</td>
                                           </tr>
                                       <?php endif; ?>
                                   </tbody>
                               </table>
                           </div>
                       </div>
                   </div>
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

                                   <!-- <button
                                       type="button"
                                       class="btn btn-outline-secondary rounded-4"
                                       id="btnBrowseFiles">
                                       <i class="lni lni-upload-1 me-2"></i> Browse Files
                                   </button> -->


                                   <!-- <p class="form-text text-danger fst-italic mt-2">You can select multiple files to upload. Allowed formats: PDF, Word, Excel, and Images (JPG, PNG, GIF, etc.).</p>
                                   <p class="form-text text-danger fst-italic fw-bold mb-0">Maximum of 10Mb per file.</p> -->
                               </div>

                               <div class="mb-3">
                                   <div id="attachment-list" style="display: block;">
                                       <table id="fileList" class="table table-striped">
                                           <thead>
                                               <tr>
                                                   <th scope="col">File Name</th>
                                                   <th scope="col">File Size</th>
                                                   <!-- <th scope="col">Action</th> -->
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

               <div class="col-md-12 ms-auto mt-4" id="trail-section">
                   <div class="card shadow-sm rounded-4 overflow-hidden">
                       <div class="card-header bg-warning text-white">
                           <h5 class="mb-0">Request Logs</h5>
                       </div>
                       <div class="card-body">
                           <div class="table-responsive">
                               <table class="table table-bordered table-hover align-middle rounded-4 overflow-hidden shadow-sm">
                                   <thead class="table text-center bg-muted">
                                       <tr>
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

               <?php if ($request_status == 'PENDING'): ?>
                   <div id="btn-section" class="text-end mt-4">
                       <button type="button" id="approver_btn" class="btn btn-success px-4 rounded-4 mb-2" data-bs-toggle="modal" data-bs-target="#ApproveModal">Approve Request</button>
                       <button type="button" id="decline_btn" class="btn btn-danger px-4 rounded-4 mb-2" data-bs-toggle="modal" data-bs-target="#DeclineModal">Decline Request</button>
                   </div>
               <?php endif; ?>

           </div>
       </div>
   </div>

   <!-- MODALS -->

   <!-- Submit -->
   <div class="modal fade" id="SubmitModal" tabindex="-1" aria-labelledby="SubmitModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-dialog-centered">
           <div class="modal-content rounded-4">
               <div class="modal-header">
                   <h5 class="modal-title" id="SubmitModalLabel">COE Request for Travel</h5>
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

   <!-- Approve Modal -->
   <div class="modal fade" id="ApproveModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="true" aria-labelledby="ApproveModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-xl modal-dialog-centered rounded-4">
           <div class="modal-content rounded-4">
               <div class="modal-header py-2 bg-success text-white">
                   <h5 class="modal-title">Approve Request</h5>
                   <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
               </div>
               <div class="modal-body" class="rounded-4 border-1 border-danger">
                   <div class="mb-2" style="max-height: 400px; overflow-y: auto;">
                       <ul id="approvalFlowList" class="list-group"></ul>
                   </div>

                   <div class="mb-2">
                       <input type="hidden" id="approver_level">
                   </div>
                   <div id="approver-access" class="d-none mt-4">
                       <div class="mb-2">
                           <label for="approval_remarks" class="form-label small">
                               Approval Remarks
                           </label>
                           <textarea class="form-control rounded-4" id="approval_remarks" rows="2" placeholder="Remarks..."></textarea>
                       </div>
                       <div class="text-end">
                           <button type="button" id="approved_request" class="btn btn-success rounded-4" data-bs-dismiss="modal">Approve</button>
                       </div>
                   </div>
                   <div id="waiting-approval-access" class="text-danger">
                   </div>
               </div>
           </div>
       </div>
   </div>

   <!-- Decline Modal -->
   <div class="modal fade" id="DeclineModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="true" aria-labelledby="DeclineModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-m modal-dialog-centered rounded-4">
           <div class="modal-content rounded-4">
               <div class="modal-header py-2 bg-danger text-white">
                   <h5 class="modal-title">Decline Request</h5>
                   <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
               </div>
               <div class="modal-body">
                   <div class="mb-2">
                       <label for="decline_reason" class="form-label small">
                           Decline Reason<span class="text-danger"> *</span>
                       </label>
                       <textarea class="form-control rounded-4" id="decline_reason" rows="2" oninput="adjustTextareaHeight(this)" placeholder="Reason for decline..."></textarea>
                   </div>
                   <div class="text-end">
                       <button type="button" id="declined_request" class="btn btn-danger rounded-4">Decline</button>
                   </div>
               </div>
           </div>
       </div>
   </div>

   <?php include '../includes/footer_upper.php'; ?>
   <script src="../assets/js/coe.js"></script>
   <script>
       document.addEventListener("DOMContentLoaded", function() {
           Fetch_ApprovalFlow('approver_btn');
           ApprovedCOERequest('approved_request');
           DeclinedCOERequest('declined_request');
       });
   </script>
   <?php include '../includes/footer_lower.php'; ?>