   <?php
    session_start();

    $user_id = isset($_SESSION['employeeid']) ? $_SESSION['employeeid'] : '';

    if (!isset($_POST['employee_id'])) {
        echo "<p>No Employee selected.</p>";
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

    if (!isset($_POST['request_id'])) {
        echo "<p>No request selected.</p>";
        exit;
    }

    // Store BR number and include the fetch logic
    $_POST['request_id'] = $_POST['request_id'];
    include 'fetch/request_coe_fetch.php';

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

    if ($coe_type === 'BENEFIT CLAIM' || $coe_type === 'BENEFIT CLAIM WITH COMPENSATION') {
        $claim_type = isset($_SESSION['claim_type']) ? $_SESSION['claim_type'] : '';
        $compensation = isset($_SESSION['compensation']) ? $_SESSION['compensation'] : '';
    }

    if ($coe_type === 'TRAVEL') {
        $travel_datefrom = isset($_SESSION['travel_datefrom']) ? $_SESSION['travel_datefrom'] : '';
        $travel_dateto = isset($_SESSION['travel_dateto']) ? $_SESSION['travel_dateto'] : '';
        $date_return = isset($_SESSION['date_return']) ? $_SESSION['date_return'] : '';
        $travel_type = isset($_SESSION['travel_type']) ? $_SESSION['travel_type'] : '';
        $travel_location = isset($_SESSION['travel_location']) ? $_SESSION['travel_location'] : '';
    }

    if ($coe_type === 'FINANCIAL') {
        $purpose_details = isset($_SESSION['purpose_details']) ? $_SESSION['purpose_details'] : '';
    }
    ?>

   <div class="container-fluid mb-2">
       <div class="card shadow rounded-4">
           <div class="card-body p-4">
               <h5 class="card-title mb-4 text-muted">COE Requisition Form for Financial:
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
                   <div class="text-end mt-4">
                       <button type="button" id="submit_btn" class="btn btn-success px-4 rounded-4 mb-2" data-bs-toggle="modal" data-bs-target="#ApproveModal">Approve Request</button>
                       <button type="button" id="submit_btn" class="btn btn-danger px-4 rounded-4 mb-2" data-bs-toggle="modal" data-bs-target="#DeclineModal">Decline Request</button>
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
       <div class="modal-dialog modal-m modal-dialog-centered rounded-4">
           <div class="modal-content rounded-4">
               <div class="modal-header py-2 bg-success text-white">
                   <h5 class="modal-title">Approve Request</h5>
                   <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
               </div>
               <div class="modal-body">
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
                       <textarea class="form-control rounded-4" id="decline_reason" rows="2" placeholder="Reason for decline..."></textarea>
                   </div>
                   <div class="text-end">
                       <button type="button" id="declined_request" class="btn btn-danger rounded-4">Decline</button>
                   </div>
               </div>
           </div>
       </div>
   </div>