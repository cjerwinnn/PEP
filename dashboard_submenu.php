<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<!-- Main Content Container -->
<div id="main-content" class="container-fluid mb-2">
    <div class="card shadow rounded-4" style="height: 90vh;">
        <div class="card-body p-4">
            <h5 class="text-muted">Dashboard</h5>

            <!-- ======================= Tile Buttons ================== -->
            <div class="cardBox position-relative">
                <div class="d-flex gap-3 flex-wrap">
                    <button onclick="loadPage('dashboard_wtms.php')" class="btn btn-light shadow rounded-4 px-4 py-3 text-start" style="width: 180px; height: 120px;">
                        <i class="bi bi-clock fs-2 text-primary"></i><br>
                        <span class="fw-semibold text-dark">WTMS</span>
                    </button>

                    <button onclick="loadPage('dashboard/coe.php')" class="btn btn-light shadow rounded-4 px-4 py-3 text-start" style="width: 180px; height: 120px;">
                        <i class="bi bi-journal-text fs-2 text-primary"></i><br>
                        <span class="fw-semibold text-dark">C.O.E</span>
                    </button>


                    <button onclick="loadPage('dashboard/bulletin_board.php')" class="btn btn-light shadow rounded-4 px-4 py-3 text-start" style="width: 180px; height: 120px;">
                        <i class="bi bi-journal-text fs-2 text-primary"></i><br>
                        <span class="fw-semibold text-dark">Bulletin Board</span>
                    </button>

                </div>
            </div>
        </div>
    </div>
</div>