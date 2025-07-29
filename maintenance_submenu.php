<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<!-- Main Content Container -->
<div id="main-content" class="container-fluid mb-2">
    <div class="card shadow rounded-4" style="height: 90vh;">
        <div class="card-body p-4">
            <h5 class="text-muted">COE Maintenance</h5>

            <!-- ======================= Tile Buttons ================== -->
            <div class="cardBox position-relative">
                <div class="d-flex gap-3 flex-wrap">
                    <button onclick="loadPage('maintenance_coe_checklist.php')" class="btn btn-light shadow rounded-4 px-4 py-3 text-start" style="width: 180px; height: 120px;">
                        <i class="bi bi-journal-text fs-2 text-primary"></i><br>
                        <span class="fw-semibold text-muted small">COE Checklist</span>
                    </button>
                </div>
            </div>

            <h5 class="text-muted">Approval Maintenance</h5>

            <!-- ======================= Tile Buttons ================== -->
            <div class="cardBox position-relative">
                <div class="d-flex gap-3 flex-wrap">
                    <button onclick="Maintenance_COE_Approval('maintenance_coe_approval.php')" class="btn btn-light shadow rounded-4 px-4 py-3 text-start" style="width: 180px; height: 120px;">
                        <i class="bi bi-journal-text fs-2 text-primary"></i><br>
                        <span class="fw-semibold text-muted small">COE Approval Flow</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>