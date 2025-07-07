<?php
session_start();

include 'config/connection.php';

$employeeid = isset($_SESSION['employeeid']) ? $_SESSION['employeeid'] : '';
?>

<div class="container-fluid mb-2">
    <div class="card shadow rounded-4">
        <div class="card-body p-4">
            <h5 class="text-muted">Certificate of Employment Request</h5>

            <button type="button" class="btn btn-primary rounded-4 mb-4" data-bs-toggle="modal" data-bs-target="#TaggingModal">Request COE</button>

            <div class="row mb-2">
                <div class="col-12 col-md-12 d-flex align-items-center gap-2">
                    <input type="text" class="form-control rounded-4" id="ceSearchBar" placeholder="Search..." onkeyup="searchCETable()">
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center gap-2">
                    <label for="ceTableLimit" class="form-label mb-0">Show</label>
                    <select id="ceTableLimit" class="form-select form-select-sm w-auto" onchange="paginateTable()">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                    </select>
                    <label class="form-label mb-0">entries</label>
                </div>
                <div id="cePageInfo" class="small text-muted"></div>
            </div>


            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm small" id="ceTable" style="table-layout: fixed; width: 100%; font-size: 0.875rem;">
                    <thead class="thead-light mb-4">
                        <tr class="bg-dark text-white text-center rounded-4">
                            <th class="bg-primary text-white" style="min-width: 30px;">Request ID</th>
                            <th class="bg-primary text-white" style="min-width: 150px;">COE Type</th>
                            <th class="bg-primary text-white" style="min-width: 180px;">Date & Time Requested</th>
                            <th class="bg-primary text-white" style="min-width: 180px;">Status</th>
                            <th class="bg-primary text-white" style="min-width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $conn->prepare("CALL COE_REQUESTLIST()");

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
                            $date = new DateTime($row['requested_date']);
                            $formattedDate = $date->format('M d, Y');

                            echo "<tr>";
                            echo "<td class='text-start text-wrap'>" . htmlspecialchars($row['request_id']) . "</td>";
                            echo "<td class='text-start text-wrap'>" . htmlspecialchars($row['coe_type']) . "</td>";
                            echo "<td class='text-center'>" . htmlspecialchars($formattedDate) . ' ' . htmlspecialchars($row['requested_time']) . "</td>";
                            echo "<td class='text-center text-wrap'>" . htmlspecialchars($row['request_status']) . "</td>";
                            echo "<td class='text-center'>";
                            echo '<button class="btn btn-warning btn-sm ms-2 rounded-4" title="View COE Request" onclick="COE_View(\'' . addslashes($row['request_id']) . '\', \'' . addslashes($row['employee_id']) . '\', \'' . addslashes($row['coe_type']) . '\')">View</button>';
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
                    <i class="bi bi-ui-checks-grid me-2"></i>Select Certificate Type
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body px-5 pb-4">
                <p class="text-secondary text-center mb-4 fs-6">
                    Please choose the purpose of the Certificate of Employment:
                </p>

                <div class="row g-4">

                    <!-- Financial -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded-4 h-100 shadow-sm">
                            <button type="button"
                                class="btn btn-outline-secondary btn-lg w-100 rounded-pill"
                                data-bs-toggle="modal"
                                data-bs-dismiss="modal"
                                onclick="COE_FinancialRequest('<?= $employeeid ?>')">
                                Financial
                            </button>
                            <div class="small text-muted mt-2 text-center">
                                For loans, credit card applications, bank requirements, and other financial-related endorsements.
                            </div>
                        </div>
                    </div>

                    <!-- Travel -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded-4 h-100 shadow-sm">
                            <button type="button"
                                class="btn btn-outline-secondary btn-lg w-100 rounded-pill"
                                data-bs-toggle="modal"
                                data-bs-dismiss="modal"
                                onclick="COE_TravelRequest('<?= $employeeid ?>')">
                                Travel
                            </button>
                            <div class="small text-muted mt-2 text-center">
                                For visa applications, or international/local travel purposes.
                            </div>
                        </div>
                    </div>

                    <!-- Training / Educational -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded-4 h-100 shadow-sm">
                            <button type="button"
                                class="btn btn-outline-secondary btn-lg w-100 rounded-pill"
                                data-bs-toggle="modal"
                                data-bs-dismiss="modal"
                                onclick="COE_TrainingRequest('<?= $employeeid ?>')">
                                Training / Educational
                            </button>
                            <div class="small text-muted mt-2 text-center">
                                For school enrollment, scholarship, or training-related documentation requirements.
                            </div>
                        </div>
                    </div>

                    <!-- Benefit Claim -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded-4 h-100 shadow-sm">
                            <button type="button"
                                class="btn btn-outline-secondary btn-lg w-100 rounded-pill"
                                data-bs-toggle="modal"
                                data-bs-dismiss="modal"
                                onclick="COE_BenefitClaimRequest('<?= $employeeid ?>')">
                                Benefit Claim
                            </button>
                            <div class="small text-muted mt-2 text-center">
                                For government, private, or health-related benefit claims (e.g., SSS, PhilHealth, Pag-ibig, Insurance).
                            </div>
                        </div>
                    </div>

                    <!-- Customized COE -->
                    <div class="col-12">
                        <div class="p-3 border border-danger-subtle rounded-4 shadow-sm h-100">
                            <button type="button"
                                class="btn btn-outline-danger btn-lg w-100 rounded-pill"
                                data-bs-toggle="modal"
                                data-bs-dismiss="modal">
                                Customized COE
                            </button>
                            <div class="small text-muted mt-2 text-center">
                                For special requests not covered above. You may specify your preferred content or format.
                            </div>
                            <div class="small text-muted mt-2 text-center">
                                <span class="fw-bold text-danger">NOTE:</span>
                                <span class="fst-italic text-danger">Certificate of employment will be issued as required by labor regulations, the company does not support their use for overseas or local employment purposes unless the employee has completed formal clearance and separation.</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>