<?php include '../includes/header.php'; ?>

<!-- Main Content Container -->
<div id="main-content" class="container-fluid mb-2">
    <div class="card shadow rounded-4" style="height: 90vh;">
        <div class="card-body p-4">
            <h5 class="text-muted">Certificate of Employment</h5>

            <!-- ======================= Tile Buttons ================== -->
            <div class="cardBox position-relative">
                <div class="d-flex gap-3 flex-wrap">
                    <a href="request_coe_list.php" class="btn btn-light shadow rounded-4 px-4 py-3 text-start" style="width: 180px; height: 120px;">
                        <i class="bi bi-journal-text fs-2 text-primary"></i><br>
                        <span class="fw-semibold text-dark">REQUEST</span>
                    </a>

                    <a href="request_coe_approval_list.php" class="btn btn-light shadow rounded-4 px-4 py-3 text-start" style="width: 180px; height: 120px;">
                        <i class="bi bi-check-circle fs-2 text-success"></i><br>
                        <span class="fw-semibold text-dark">APPROVAL</span>
                    </a>

                    <a href="request_coe_hr_list.php" class="btn btn-light shadow rounded-4 px-4 py-3 text-start" style="width: 180px; height: 120px;">
                        <i class="bi bi-person-badge fs-2 text-secondary"></i><br>
                        <span class="fw-semibold text-dark">HR</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer_upper.php'; ?>
<script src="../assets/js/coe.js"></script>
<?php include '../includes/footer_lower.php'; ?>