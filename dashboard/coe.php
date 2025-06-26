<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<?php
$status_counts = [
    'requested' => 0,
    'approved' => 0,
    'onprocess' => 0,
    'onhold' => 0,
    'forsigning' => 0,
    'forreleasing' => 0,
    'released' => 0,
    'denied' => 0
];
?>

<style>
    .coe-card {
        padding: 1.5rem;
        border-radius: 1rem;
        background: #f8f9fa;
        box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease;
    }

    .coe-card:hover {
        background: #f1f3f5;
        transform: translateY(-3px);
    }

    .numbers {
        font-size: 2rem;
        font-weight: 700;
        color: #343a40;
    }

    .cardName {
        font-size: 1rem;
        color: #6c757d;
    }

    .status-icon {
        font-size: 2.5rem;
        margin-bottom: 0.4rem;
        color: #495057;
    }
</style>

<div id="main-content" class="container-fluid mb-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">COE Dashboard</h4>

        <!-- Filter Dropdown -->
        <select class="form-select w-auto rounded-4" id="statusFilter" onchange="filterStatus(this.value)">
            <option value="all">All</option>
            <option value="requested">Requested</option>
            <option value="approved">Approved</option>
            <option value="onprocess">On Process</option>
            <option value="onhold">On Hold</option>
            <option value="forsigning">For Signing</option>
            <option value="forreleasing">For Releasing</option>
            <option value="released">Released</option>
            <option value="denied">Denied</option>
        </select>
    </div>

    <div class="row g-4" id="statusCards">

        <div class="col-6 col-md-4 col-lg-3 status-card" data-status="requested">
            <div class="coe-card text-center">
                <i class="bi bi-hourglass-split status-icon text-warning"></i>
                <div class="numbers text-warning" id="count-requested"><?= $status_counts['requested'] ?></div>
                <div class="cardName">Requested</div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-3 status-card" data-status="approved">
            <div class="coe-card text-center">
                <i class="bi bi-check-circle-fill status-icon text-success"></i>
                <div class="numbers text-success" id="count-approved"><?= $status_counts['approved'] ?></div>
                <div class="cardName">Approved</div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-3 status-card" data-status="onprocess">
            <div class="coe-card text-center">
                <i class="bi bi-gear-fill status-icon text-info"></i>
                <div class="numbers text-info" id="count-onprocess"><?= $status_counts['onprocess'] ?></div>
                <div class="cardName">On Process</div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-3 status-card" data-status="onhold">
            <div class="coe-card text-center">
                <i class="bi bi-pause-circle status-icon text-secondary"></i>
                <div class="numbers text-secondary" id="count-onhold"><?= $status_counts['onhold'] ?></div>
                <div class="cardName">On Hold</div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-3 status-card" data-status="forsigning">
            <div class="coe-card text-center">
                <i class="bi bi-pen-fill status-icon text-primary"></i>
                <div class="numbers text-primary" id="count-forsigning"><?= $status_counts['forsigning'] ?></div>
                <div class="cardName">For Signing</div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-3 status-card" data-status="forreleasing">
            <div class="coe-card text-center">
                <i class="bi bi-send-check-fill status-icon text-primary"></i>
                <div class="numbers text-primary" id="count-forreleasing"><?= $status_counts['forreleasing'] ?></div>
                <div class="cardName">For Releasing</div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-3 status-card" data-status="released">
            <div class="coe-card text-center">
                <i class="bi bi-file-earmark-check-fill status-icon text-success"></i>
                <div class="numbers text-success" id="count-released"><?= $status_counts['released'] ?></div>
                <div class="cardName">Released</div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-3 status-card" data-status="denied">
            <div class="coe-card text-center">
                <i class="bi bi-x-circle-fill status-icon text-danger"></i>
                <div class="numbers text-danger" id="count-denied"><?= $status_counts['denied'] ?></div>
                <div class="cardName">Denied</div>
            </div>
        </div>

    </div>
</div>

<div id="main-content" class="container-fluid mb-2 mt-4">

    <h5 class="mb-4">Recent COE Activity</h5>

    <div class="table-responsive shadow-sm rounded-4">
        <?php
        include '../config/connection.php'; // adjust path if needed

        $stmt = $conn->prepare("CALL DASHBOARD_COE_LOGS()");
        $stmt->execute();
        $result = $stmt->get_result();
        ?>

        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th scope="col" style="cursor:pointer" onclick="sortTable(0)">Request ID <i class="bi bi-arrow-down-up"></i></th>
                    <th scope="col" style="cursor:pointer" onclick="sortTable(1)">Employee Name <i class="bi bi-arrow-down-up"></i></th>
                    <th scope="col" style="cursor:pointer" onclick="sortTable(2)">Status <i class="bi bi-arrow-down-up"></i></th>
                    <th scope="col" style="cursor:pointer" onclick="sortTable(3)">Date & Time <i class="bi bi-arrow-down-up"></i></th>
                    <th scope="col">Remarks</th>
                </tr>
            </thead>
            <tbody id="activityBody">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr data-status="<?= strtolower(str_replace(' ', '', $row['request_status'])) ?>">
                        <td><?= htmlspecialchars($row['request_id']) ?></td>
                        <td><?= htmlspecialchars($row['employee_name']) ?></td>
                        <td>
                            <?php
                            $status = $row['request_status'];
                            $badgeClass = match (strtolower($status)) {
                                'requested' => 'bg-warning text-dark',
                                'approved' => 'bg-success',
                                'on process' => 'bg-info text-dark',
                                'on hold' => 'bg-secondary',
                                'for signing' => 'bg-primary',
                                'for releasing' => 'bg-primary',
                                'released' => 'bg-success',
                                'denied' => 'bg-danger',
                                default => 'bg-light text-dark',
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars(ucwords($status)) ?></span>
                        </td>
                        <td><?= htmlspecialchars($row['action_date']) . ' ' . htmlspecialchars($row['action_time']) ?></td>
                        <td><?= htmlspecialchars($row['action_remarks']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php
        $stmt->close();
        $conn->close();
        ?>

    </div>
</div>