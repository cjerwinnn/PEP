<?php
session_start();

include 'config/connection.php';

$employeeid = isset($_SESSION['employeeid']) ? $_SESSION['employeeid'] : '';
?>

<div class="container-fluid mb-2">
    <div class="card shadow rounded-4">
        <div class="card-body p-4">
            <h5 class="text-muted text-center">COE Requirements Checklist Maintenance</h5>

            <button type="button" class="btn btn-primary rounded-4 mb-4" data-bs-toggle="modal" data-bs-target="#TaggingModal">Add Requirement to Checklist</button>

            <div class="row mb-2">
                <div class="col-12 col-md-5 d-flex align-items-center gap-2">
                    <input type="text" class="form-control rounded-4" id="ceSearchBar" placeholder="Search..." onkeyup="maintenance_coechecklist_filter()">
                </div>
                <div class="col-12 col-md-3 d-flex align-items-center gap-2">
                    <select class="form-select rounded-4" id="ceFilterDropdown" onchange="maintenance_coechecklist_filter()">
                        <option value="" selected>***All Types***</option>
                        <option value="BENEFITS CLAIM">Benefits Claims</option>
                        <option value="FINANCIAL">Financial</option>
                        <option value="TRAVEL">Travel</option>
                        <option value="TRAINING/EDUCATIONAL">Training/Educational</option>

                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center gap-2">
                    <label for="maintenance_checklist_tableLimit" class="form-label mb-0">Show</label>
                    <select id="maintenance_checklist_tableLimit" class="form-select form-select-sm w-auto" onchange="paginateTable()">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                    </select>
                    <label class="form-label mb-0">entries</label>
                </div>
                <div id="cePageInfo" class="small text-muted"></div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm small" id="maintenance_checklist_table" style="table-layout: fixed; width: 100%; font-size: 0.875rem;">
                    <thead class="thead-light mb-4">
                        <tr class="bg-dark text-white text-center rounded-4">
                            <th class="bg-primary text-white" style="width: 20%;">COE Type</th>
                            <th class="bg-primary text-white" style="width: 20%;">Requirements</th>
                            <th class="bg-primary text-white" style="width: 50%;">Description</th>
                            <th class="bg-primary text-white" style="width: 10%;">Required?</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $conn->prepare("CALL MAINTENANCE_COE_CHECKLIST()");

                        if (!$stmt) {
                            die("Prepare failed: " . $conn->error);
                        }

                        if (!$stmt->execute()) {

                            die("Execute failed: " . $stmt->error);
                        }

                        $result = $stmt->get_result();
                        if (!$result) {
                            die("Getting result set failed: " . $stmt->error);
                        }

                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td class='text-start text-wrap'>" . htmlspecialchars($row['coe_type']) . "</td>";
                            echo "<td class='text-start text-wrap'>" . htmlspecialchars($row['requirements_name']) . "</td>";
                            echo "<td class='text-start text-wrap'>" . htmlspecialchars($row['requirements_description']) . "</td>";
                            $required = $row['checklist_required'] == '1';
                            $toggleId = 'required_switch_' . uniqid();

                            echo "<td class='text-center'>";
                            echo "<div class='form-check form-switch d-flex justify-content-center'>";
                            echo "<input class='form-check-input' type='checkbox' role='switch' id='{$toggleId}'" . ($required ? ' checked' : '') . ">";
                            echo "</div>";
                            echo "</td>";
                            echo "</tr>";
                        }

                        $stmt->close();
                        $conn->close();
                        ?>
                    </tbody>
                </table>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div id="ceTablePagination" class="small d-flex align-items-center gap-1">
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <button class="btn btn-outline-secondary btn-sm rounded-4" onclick="firstPage()">⏮ First</button>
                            <button class="btn btn-outline-secondary btn-sm rounded-4" onclick="prevPage()">◀ Prev</button>
                            <button class="btn btn-outline-secondary btn-sm rounded-4" onclick="nextPage()">Next ▶</button>
                            <button class="btn btn-outline-secondary btn-sm rounded-4" onclick="lastPage()">Last ⏭</button>
                        </div>
                        <span class="ms-3" id="cePageInfo"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tagging Modal -->
<div class="modal fade" id="TaggingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="true" aria-labelledby="TaggingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg rounded-4 border-0">

            <!-- Modal Header -->
            <div class="modal-header bg-light py-3 rounded-top-4 border-bottom">
                <h5 class="modal-title fw-bold text-primary">
                    <i class="bi bi-paperclip me-2"></i>Add Requirement to Checklist
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body px-5 pb-4">

                <div class="row g-4 mt-1">

                    <div class="col-12 col-md-6 d-flex align-items-center gap-2">
                        <select class="form-select rounded-4" id="add_checklist_coe_type">
                            <option value="" selected disabled>*** Please select COE Type ***</option>
                            <option value="BENEFITS CLAIM">Benefits Claims</option>
                            <option value="BENEFITS CLAIM WITH COMPENSATION">Benefits Claims w/ Compensation</option>
                            <option value="FINANCIAL">Financial</option>
                            <option value="TRAVEL">Travel</option>
                            <option value="TRAINING/EDUCATIONAL">Training/Educational</option>

                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small">Name of Required Document<span class="text-danger"> *</span></label>
                        <input type="text" class="form-control rounded-4" id="requirement_name" placeholder="e.g., Attachment of Endorsement Letter">
                    </div>


                    <div class="mb-2">
                        <label for="req_reason" class="form-label small">
                            Requirement Description<span class="text-danger"> *</span>
                        </label>
                        <textarea class="form-control rounded-4" id="req_reason" rows="2" oninput="adjustTextareaHeight(this)" placeholder="e.g., Attach a signed endorsement letter from your department staff, supervisor, or head indicating that the endorsement has been completed."></textarea>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault" checked>
                            <label class="form-check-label" for="flexSwitchCheckDefault">Required?</label>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="button" class="btn btn-primary btn-sm rounded-4 mb-4" style="width: 200px;" onclick="InsertCOERequirements(this)">Implement Requirement</button>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>