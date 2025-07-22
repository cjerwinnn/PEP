<?php
include 'config/connection.php';
?>

<div class="container-fluid mb-2">
    <div class="card shadow rounded-4">
        <div class="card-body p-4">
            <h5 class="text-muted">Certificate of Employment Request Approval</h5>

            <div class="row mb-2">
                <div class="col-12 col-md-12 d-flex align-items-center gap-2">
                    <input type="text" class="form-control rounded-4" id="ceSearchBar" placeholder="Search..." onkeyup="searchCETable()">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm small" id="ceTable" style="table-layout: fixed; width: 100%; font-size: 0.875rem;">
                    <thead class="thead-light mb-4">
                        <tr class="bg-dark text-white text-center rounded-4">
                            <th class="bg-primary text-white" style="min-width: 30px;">Request ID</th>
                            <th class="bg-primary text-white" style="min-width: 150px;">Employee</th>
                            <th class="bg-primary text-white" style="min-width: 30px;">COE Type</th>
                            <th class="bg-primary text-white" style="min-width: 120px;">Date & Time Requested</th>
                            <th class="bg-primary text-white" style="min-width: 100px;">Status</th>
                            <th class="bg-primary text-white" style="min-width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $conn->prepare("CALL COE_APPROVALLIST_SUPERVISOR()");

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
                            echo "<td class='text-start text-wrap'>" . htmlspecialchars($row['employee']) . "</td>";
                            echo "<td class='text-start text-wrap'>" . htmlspecialchars($row['coe_type']) . "</td>";
                            echo "<td class='text-center'>" . htmlspecialchars($formattedDate) . ' ' . htmlspecialchars($row['requested_time']) . "</td>";
                            echo "<td class='text-center text-wrap'>" . htmlspecialchars($row['request_status']) . "</td>";
                            echo "<td class='text-center'>";
                            echo '<button class="btn btn-warning btn-sm ms-2 rounded-4" title="View COE Request" onclick="COE_ApprovalView(\'' . addslashes($row['request_id']) . '\', \'' . addslashes($row['employee_id']) . '\', \'' . addslashes($row['coe_type']) . '\')">View</button>';
                            echo "</td>";
                            echo "</tr>";
                        }

                        $stmt->close();
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>