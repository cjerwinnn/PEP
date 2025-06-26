   <?php

    session_start();

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
        $compensation_details = isset($_SESSION['compensation_details']) ? $_SESSION['compensation_details'] : '';
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

    if ($coe_type === 'TRAINING/EDUCATIONAL') {
        $employee_title = isset($_SESSION['employee_title']) ? $_SESSION['employee_title'] : '';
        $purpose_details = isset($_SESSION['purpose_details']) ? $_SESSION['purpose_details'] : '';
    }
    ?>

   <div class="container-fluid mb-2">
       <div class="card shadow rounded-4">
           <div class="card-body p-4">
               <h5 class="card-title mb-4 text-muted">COE Requisition Form for TRAINING/EDUCATIONAL:
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

               <div class="d-flex align-items-center my-4">
                   <hr class="flex-grow-1">
                   <h5 class="mx-3 mb-0 text-muted small">Purpose Information</h5>
                   <hr class="flex-grow-1">
               </div>


               <div class="mb-2">
                   <label class="form-label small">Employee Title</label>
                   <input type="text"
                       class="form-control rounded-4"
                       id="emp_title"
                       data-original="<?= htmlspecialchars($employee_title) ?>"
                       value="<?php echo htmlspecialchars($employee_title); ?>"
                       placeholder="Example: RN, RMT, MD"
                       disabled>
               </div>

               <div class="mb-2">
                   <label for="compensation_details" class="form-label small">
                       Purpose Details<span class="text-danger"> * </span><span class="text-muted fst-italic">(Encoded by HR)</span>
                   </label>
                   <div id="compensation_details"
                       class="form-control rounded-4"
                       contenteditable="false"
                       style="min-height: 80px; background-color: #e9ecef;">
                       <?= $purpose_details ?>
                   </div>

               </div>

               <button type="button" id="btn_edit_compensation" class="btn btn-outline-primary rounded-4 px-5 mb-4">
                   Edit Purpose Details
               </button>

               <button type="button" id="btn_update_compensation" class="btn btn-primary rounded-4 px-5 mb-4" style="display: none;">
                   Update Purpose Details
               </button>

               <button type="button" id="btn_canceledit_compensation" class="btn btn-outline-danger rounded-4 px-5 mb-4" style="display: none;">
                   Cancel edit
               </button>


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

               <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">

                   <!-- Right side (auto margin to push right) -->
                   <div class="ms-auto d-flex gap-2 flex-wrap">
                       <button type="button" id="btn_generate_coe" class="btn btn-outline-dark rounded-4 px-5">
                           VIEW/PRINT COE
                       </button>

                       <?php if ($request_status != 'RELEASED'): ?>
                           <button type="button" id="submit_btn" class="btn btn-secondary text-white px-4 rounded-4" data-bs-toggle="modal" data-bs-target="#TaggingModal">
                               Request Tagging
                           </button>
                       <?php endif; ?>
                   </div>
               </div>

           </div>
       </div>
   </div>

   <!-- Tagging Modal -->
   <div class="modal fade" id="TaggingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="true" aria-labelledby="TaggingModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-m modal-dialog-centered rounded-4">
           <div class="modal-content rounded-4">
               <div class="modal-header py-2 bg-muted text-dark">
                   <h5 class="modal-title">Request Status Tagging</h5>
                   <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
               </div>
               <div class="modal-body text-center">

                   <!-- Status Buttons -->
                   <div class="d-flex flex-wrap justify-content-center gap-2">

                       <?php if (in_array($request_status, ['PENDING', 'APPROVED'])): ?>

                           <button type="button" class="btn btn-outline-primary rounded-4 px-4"
                               data-bs-toggle="modal" data-bs-target="#OnProcessModal" data-bs-dismiss="modal">
                               ON PROCESS
                           </button>

                           <button type="button" class="btn btn-outline-secondary rounded-4 px-4"
                               data-bs-toggle="modal" data-bs-target="#OnHoldModal" data-bs-dismiss="modal">
                               ON HOLD
                           </button>

                       <?php endif; ?>

                       <?php if ($request_status == 'ON HOLD'): ?>

                           <button type="button" class="btn btn-outline-primary rounded-4 px-4"
                               data-bs-toggle="modal" data-bs-target="#OnProcessModal" data-bs-dismiss="modal">
                               ON PROCESS
                           </button>

                           <button type="button" class="btn btn-outline-danger rounded-4 px-4"
                               data-bs-toggle="modal" data-bs-target="#DeniedModal" data-bs-dismiss="modal">
                               DENIED
                           </button>

                       <?php endif; ?>


                       <?php if ($request_status == 'FOR SIGNING'): ?>

                           <button type="button" class="btn btn-outline-info rounded-4 px-4"
                               data-bs-toggle="modal" data-bs-target="#ForReleasingModal" data-bs-dismiss="modal">
                               FOR RELEASING
                           </button>

                           <button type="button" class="btn btn-outline-danger rounded-4 px-4"
                               data-bs-toggle="modal" data-bs-target="#DeniedModal" data-bs-dismiss="modal">
                               DENIED
                           </button>

                       <?php endif; ?>



                       <?php if ($request_status == 'ON PROCESS'): ?>

                           <button type="button" class="btn btn-outline-warning rounded-4 px-4"
                               data-bs-toggle="modal" data-bs-target="#ForSigningModal" data-bs-dismiss="modal">
                               FOR SIGNING
                           </button>

                           <button type="button" class="btn btn-outline-info rounded-4 px-4"
                               data-bs-toggle="modal" data-bs-target="#ForReleasingModal" data-bs-dismiss="modal">
                               FOR RELEASING
                           </button>

                       <?php endif; ?>


                       <?php if ($request_status == 'ON PROCESS' || $request_status == 'FOR RELEASING'): ?>

                           <button type="button" class="btn btn-outline-success rounded-4 px-4"
                               data-bs-toggle="modal" data-bs-target="#ReleasedModal" data-bs-dismiss="modal">
                               RELEASED
                           </button>

                           <button type="button" class="btn btn-outline-secondary rounded-4 px-4"
                               data-bs-toggle="modal" data-bs-target="#OnHoldModal" data-bs-dismiss="modal">
                               ON HOLD
                           </button>

                       <?php endif; ?>

                       <?php if ($request_status == 'PENDING' || $request_status == 'APPROVED' || $request_status == 'ON PROCESS' || $request_status == 'FOR RELEASING'): ?>

                           <button type="button" class="btn btn-outline-danger rounded-4 px-4"
                               data-bs-toggle="modal" data-bs-target="#DeniedModal" data-bs-dismiss="modal">
                               DENIED
                           </button>

                       <?php endif; ?>

                   </div>
               </div>

           </div>
       </div>
   </div>

   <!-- On Process Modal -->
   <div class="modal fade" id="OnProcessModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="true" aria-labelledby="OnProcessModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-dialog-centered modal-md">
           <div class="modal-content rounded-4">

               <!-- Modal Header -->
               <div class="modal-header bg-primary text-white py-2 rounded-top-4">
                   <h5 class="modal-title" id="OnProcessModalLabel">On Process</h5>
                   <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>

               <!-- Modal Body -->
               <div class="modal-body">

                   <!-- Remarks Textarea -->
                   <div class="mb-3">
                       <textarea class="form-control rounded-4" id="onprocess_remarks" rows="3" placeholder="Remarks..."></textarea>
                   </div>

                   <!-- Footer Buttons -->
                   <div class="text-end">
                       <button type="button" id="onprocess_request" class="btn btn-primary rounded-4">Tag as ON PROCESS</button>
                   </div>

               </div>
           </div>
       </div>
   </div>

   <!-- For Signing Modal -->
   <div class="modal fade" id="ForSigningModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="true" aria-labelledby="ForSigningModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-m modal-dialog-centered rounded-4">
           <div class="modal-content rounded-4">
               <div class="modal-header py-2 bg-warning text-white">
                   <h5 class="modal-title">For Singing</h5>
                   <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
               </div>
               <div class="modal-body">
                   <div class="mb-2">
                       <textarea class="form-control rounded-4" id="forsigning_remarks" rows="2" placeholder="Remarks..."></textarea>
                   </div>
                   <div class="text-end">
                       <button type="button" id="forsigning_request" class="btn btn-outline-warning rounded-4" data-bs-dismiss="modal">Tag as FOR SIGNING</button>
                   </div>
               </div>
           </div>
       </div>
   </div>


   <!-- For Releasing Modal -->
   <div class="modal fade" id="ForReleasingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="true" aria-labelledby="ForReleasingModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-m modal-dialog-centered rounded-4">
           <div class="modal-content rounded-4">
               <div class="modal-header py-2 bg-info text-white">
                   <h5 class="modal-title">For Releasing</h5>
                   <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
               </div>
               <div class="modal-body">
                   <div class="mb-2">
                       <textarea class="form-control rounded-4" id="forreleasing_remarks" rows="2" placeholder="Remarks..."></textarea>
                   </div>
                   <div class="text-end">
                       <button type="button" id="forreleasing_request" class="btn btn-outline-info rounded-4" data-bs-dismiss="modal">Tag as FOR RELEASING</button>
                   </div>
               </div>
           </div>
       </div>
   </div>


   <!-- Release Modal -->
   <div class="modal fade" id="ReleasedModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="true" aria-labelledby="ReleasedModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-m modal-dialog-centered rounded-4">
           <div class="modal-content rounded-4">
               <div class="modal-header py-2 bg-success text-white">
                   <h5 class="modal-title">Release</h5>
                   <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
               </div>
               <div class="modal-body">
                   <div class="mb-2">
                       <textarea class="form-control rounded-4" id="released_remarks" rows="2" placeholder="Remarks..."></textarea>
                   </div>
                   <div class="text-end">
                       <button type="button" id="released_request" class="btn btn-outline-success rounded-4" data-bs-dismiss="modal">Tag as RELEASED</button>
                   </div>
               </div>
           </div>
       </div>
   </div>

   <!-- On Hold Modal -->
   <div class="modal fade" id="OnHoldModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="true" aria-labelledby="OnHoldModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-m modal-dialog-centered rounded-4">
           <div class="modal-content rounded-4">
               <div class="modal-header py-2 bg-secondary text-white">
                   <h5 class="modal-title">On Hold</h5>
                   <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
               </div>
               <div class="modal-body">
                   <div class="mb-2">
                       <textarea class="form-control rounded-4" id="onhold_remarks" rows="2" placeholder="Waiting for requester to submit necessary documents...."></textarea>
                   </div>
                   <div class="text-end">
                       <button type="button" id="onhold_request" class="btn btn-outline-secondary rounded-4" data-bs-dismiss="modal">Tag as ON HOLD</button>
                   </div>
               </div>
           </div>
       </div>
   </div>


   <!-- Denied Modal -->
   <div class="modal fade" id="DeniedModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="true" aria-labelledby="DeniedModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-m modal-dialog-centered rounded-4">
           <div class="modal-content rounded-4">
               <div class="modal-header py-2 bg-danger text-white">
                   <h5 class="modal-title">Denied</h5>
                   <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
               </div>
               <div class="modal-body">
                   <div class="mb-2">
                       <textarea class="form-control rounded-4" id="denied_remarks" rows="2" placeholder="(REQUIRED) Remarks..."></textarea>
                   </div>
                   <div class="text-end">
                       <button type="button" id="denied_request" class="btn btn-outline-danger rounded-4" data-bs-dismiss="modal">Tag as DENIED</button>
                   </div>
               </div>
           </div>
       </div>
   </div>

   <!-- Approve Modal -->
   <div class="modal fade" id="ApproveModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="true" aria-labelledby="ApproveModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-m modal-dialog-centered rounded-4">
           <div class="modal-content rounded-4">
               <div class="modal-header py-2 bg-success text-white">
                   <h5 class="modal-title">Decline Request</h5>
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

   <!-- Leave Modal -->

   <div class="modal fade" id="leaveModal" tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-xl modal-dialog-centered rounded-4">
           <div class="modal-content rounded-4">
               <div class="modal-header bg-primary text-white">
                   <h5 class="modal-title" id="leaveModalLabel">Approved Leave</h5>
                   <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body p-3">
                   <div class="table-responsive">
                       <table class="table table-bordered table-striped table-sm" id="leaveTable" style="table-layout: fixed; width: 100%;">
                           <thead>
                               <tr>
                                   <th style="width: 10%;">Leave ID</th>
                                   <th style="width: 15%;">Date</th>
                                   <th style="width: 10%;">Duration</th>
                                   <th style="width: 15%;">Type</th>
                                   <th style="width: 20%;">Reason</th>
                                   <th style="width: 10%;">Status</th>
                                   <th class="text-center" style="width: 10%;">Action</th>
                               </tr>
                           </thead>
                           <tbody class="small">
                               <!-- Rows -->
                           </tbody>
                       </table>
                   </div>

               </div>
           </div>
       </div>
   </div>