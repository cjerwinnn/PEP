<div class="container-fluid mb-2">
    <div class="card shadow rounded-4" style="height: 90vh;">
        <div class="card-body p-4">
            <h5 class="text-muted">Request Approval</h5>

            <!-- ======================= Cards ================== -->
            <div class="cardBox position-relative">

                <!-- COE Button -->
                <button type="button" id="coe-list" data-page="request_coe_approval_list.php" class="btn btn-outline-primary text-start p-4 rounded-4 shadow-sm d-flex justify-content-between align-items-center position-relative w-100 mb-3">
                    <!-- Notification Badge -->
                    <span id="coe-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                        style="font-size: 0.85rem; padding: 6px 10px;">
                        3
                    </span>

                    <div class="fs-1 me-4 text-secondary text-start">
                        <ion-icon name="document-outline"></ion-icon>
                    </div>
                    <div>
                        <div class="fs-5 fw-bold">Certificate of Employment (COE)</div>
                        <div class="text-muted">A Certificate of Employment is commonly used for job applications, visa processing, bank loans, rental agreements, government transactions, and internal HR requirements as proof of a person’s employment status.</div>
                    </div>
                </button>

                <!-- Form 2316 Button -->
                <button type="button" class="btn btn-outline-primary text-start p-4 rounded-4 shadow-sm d-flex justify-content-between align-items-center position-relative w-100">
                    <!-- Notification Badge -->
                    <span id="form2316-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                        style="font-size: 0.85rem; padding: 6px 10px;">
                        1
                    </span>

                    <div class="fs-1 me-4 text-secondary">
                        <ion-icon name="document-outline"></ion-icon>
                    </div>
                    <div>
                        <div class="fs-5 fw-bold">Form 2316</div>
                        <div class="text-muted">Form 2316 is used as proof of income and tax paid for loan applications, visa processing, tax filing, and when transferring to a new job.</div>
                    </div>
                </button>

            </div>
        </div>
    </div>
</div>