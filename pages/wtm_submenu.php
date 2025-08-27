<?php
include '../includes/header.php';
?>
<!-- Main Content Container -->
<div id="main-content" class="container-fluid mb-2">
    <div class="card shadow rounded-4" style="height: 90vh;">
        <div class="card-body p-4">
            <h5 class="text-muted">Work Time Management</h5>

            <!-- ======================= Tile Buttons ================== -->
            <div class="cardBox position-relative">
                <div class="d-flex gap-3 flex-wrap">

                    <a href="../pages/wtm_mydtr.php" class="btn btn-light shadow rounded-4 px-4 py-3 text-start" style="width: 180px; height: 120px;">
                        <i class="bi bi-journal-text fs-2 text-primary"></i><br>
                        <span class="fw-semibold text-dark small text-center">My DTR</span>
                    </a>

                    <a href="../pages/shiftschedule_employee_list.php" class="btn btn-light shadow rounded-4 px-4 py-3 text-start" style="width: 180px; height: 120px;">
                        <i class="bi bi-journal-text fs-2 text-primary"></i><br>
                        <span class="fw-semibold text-dark small">Schedule</span>
                    </a>

                    <button onclick="loadPage('request_coe_hr_list.php')" class="btn btn-light shadow rounded-4 px-4 py-3 text-start" style="width: 180px; height: 120px;">
                        <i class="bi bi-journal-text fs-2 text-primary"></i><br>
                        <span class="fw-semibold text-dark">Overtime</span>
                    </button>

                    <button onclick="loadPage('request_coe_hr_list.php')" class="btn btn-light shadow rounded-4 px-4 py-3 text-start" style="width: 180px; height: 120px;">
                        <i class="bi bi-journal-text fs-2 text-primary"></i><br>
                        <span class="fw-semibold text-dark">Leave</span>
                    </button>
                </div>
            </div>

            <h5 class="text-muted">Work Time Management - HR</h5>

            <!-- ======================= Tile Buttons ================== -->
            <div class="cardBox position-relative">
                <div class="d-flex gap-3 flex-wrap">

                    <button onclick="loadPage('request_coe_list.php')" class="btn btn-light shadow rounded-4 px-4 py-3 text-start" style="width: 180px; height: 120px;">
                        <i class="bi bi-journal-text fs-2 text-primary"></i><br>
                        <span class="fw-semibold text-dark small text-center">Employee DTR</span>
                    </button>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer_upper.php'; ?>
<?php include '../includes/footer_lower.php'; ?>