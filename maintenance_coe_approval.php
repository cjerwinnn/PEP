<?php
session_start();

include 'config/connection.php';

$employeeid = isset($_SESSION['employeeid']) ? $_SESSION['employeeid'] : '';
?>

<div class="container-fluid mb-2">
    <div class="card shadow rounded-4">
        <div class="card-body p-4">
            <h5 class="text-muted text-center">COE Approval Flow Maintenance</h5>

            <div class="row mb-2">
                <div class="col-12 col-md-3 d-flex align-items-center gap-2">
                    <select class="form-select rounded-4" id="departmentFilterDropdown" onchange="onDepartmentChange()">
                        <option value="">Loading departments...</option>
                    </select>
                </div>

                <div class="col-12 col-md-3 d-flex align-items-center gap-2">
                    <select class="form-select rounded-4" id="areaFilterDropdown" onchange="onAreaChange()" disabled>
                        <option value="">Select Department first</option>
                    </select>
                </div>

                <div class="col-12 col-md-5 d-flex align-items-center gap-2">
                    <input type="text" class="form-control rounded-4" id="ceSearchBar" placeholder="Search..." onkeyup="maintenance_coechecklist_filter()">
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

            <div id="add-button-container" class="d-none">
                <button type="button" class="btn btn-primary rounded-4 mb-4 mt-2" data-bs-toggle="modal" data-bs-target="#TaggingModal">Add Approval Flow</button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm small" id="maintenance_approvalflow_table" style="table-layout: fixed; width: 100%; font-size: 0.875rem;">
                    <thead class="thead-light mb-4">
                        <tr class="bg-dark text-white text-center rounded-4">
                            <th class="bg-primary text-white" style="width: 10%;">Approval Level</th>
                            <th class="bg-primary text-white" style="width: 50%;">Approver</th>
                            <th class="bg-primary text-white" style="width: 15%;">Department</th>
                            <th class="bg-primary text-white" style="width: 15%;">Area</th>
                            <th class="bg-primary text-white" style="width: 15%;">Position</th>
                            <th class="bg-primary text-white" style="width: 10%;">Override Access</th>
                            <th class="bg-primary text-white" style="width: 10%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>

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
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content shadow-lg rounded-4 border-0">

            <!-- Modal Header -->
            <div class="modal-header bg-light py-3 rounded-top-4 border-bottom">
                <h5 class="modal-title fw-bold text-primary">
                    <i class="bi bi-paperclip me-2"></i>Manage Approval Flow
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body px-5 pb-4">

                <div class="row g-4">

                    <div>
                        <label class="form-label small">Area</label>
                        <input type="text" class="form-control rounded-4 text-danger fw-bold" id="form_approvalform_area" readonly disabled>
                    </div>

                    <input type="hidden" class="form-control rounded-4 text-danger fw-bold" id="form_approvalform_approvallevel" readonly disabled>

                    <input type="text" class="form-control rounded-4" id="add_approvallevel_search" placeholder="Search...">

                    <div style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-bordered table-hover table-sm small" style="font-size: 0.875rem; border-collapse: collapse; width: 100%; table-layout: fixed;">
                            <thead class="text-center">
                                <tr class="bg-dark text-white text-center rounded-4">
                                    <th class="bg-primary text-white" style="width: 10%;">Level</th>
                                    <th class="bg-primary text-white" style="width: 19%;">ID</th>
                                    <th class="bg-primary text-white" style="width: 25%;">Employee</th>
                                    <th class="bg-primary text-white" style="width: 27%;">Department</th>
                                    <th class="bg-primary text-white" style="width: 25%;">Area</th>
                                    <th class="bg-primary text-white" style="width: 25%;">Position</th>
                                    <th class="bg-primary text-white" style="width: 10%;">Override?</th>
                                </tr>
                            </thead>
                            <tbody id="employees_tbody">
                                <?php
                                $stmt = $conn->prepare("CALL REF_ACTIVE_EMPLOYEES()");
                                if (!$stmt) die("Prepare failed: " . $conn->error);
                                if (!$stmt->execute()) die("Execute failed: " . $stmt->error);
                                $result = $stmt->get_result();
                                if (!$result) die("Getting result set failed: " . $stmt->error);
                                while ($row = $result->fetch_assoc()) {
                                    $employeeid = htmlspecialchars($row['employeeid']);
                                    echo "<tr>";
                                    echo "<td class='text-center text-primary fw-bold' style='width:10%;' id='level_$employeeid'></td>";
                                    echo "<td class='text-center' style='width:19%;'>";
                                    echo "<input type='checkbox' class='form-check-input level-checkbox' data-employeeid='$employeeid'>&nbsp;&nbsp;$employeeid";
                                    echo "</td>";
                                    echo "<td class='text-start text-wrap' style='width:25%;'>" . htmlspecialchars($row['employeename']) . "</td>";
                                    echo "<td class='text-start text-wrap' style='width:27%;'>" . htmlspecialchars($row['department']) . "</td>";
                                    echo "<td class='text-start text-wrap' style='width:25%;'>" . htmlspecialchars($row['area']) . "</td>";
                                    echo "<td class='text-start text-wrap' style='width:25%;'>" . htmlspecialchars($row['position']) . "</td>";
                                    echo "<td class='text-center' style='width:10%;'>";
                                    echo "<div class='form-check form-switch text-center'>";
                                    echo "<input class='form-check-input override-switch' type='checkbox' id='override_$employeeid' data-employeeid='$employeeid' disabled>";
                                    echo "</div>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-center">
                        <button type="button" class="btn btn-primary btn-sm rounded-4 mb-4" style="width: 200px;" onclick="collectAndSubmitApprovers('COE', 'inserts/maintenance_coe_approvalflow_insert.php');">Add Approvers</button>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>