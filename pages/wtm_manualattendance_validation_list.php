<?php
include '../includes/header.php';
?>

<div class="container-fluid mb-2">
    <div class="card shadow rounded-4">
        <div class="card-body p-4">

            <div class="d-flex align-items-center mb-3">
                <a href="wtm_submenu.php" class="btn btn-outline-secondary btn-sm rounded-4">
                    ← Back
                </a>
                <h5 class="mb-0 ms-3 text-muted text-center">Manual In/Out Validation</h5>
            </div>

            <div class="row mb-2">

                <div class="col-6">
                    <label for="searchBar" class="form-label">Search</label>
                    <input type="text" class="form-control rounded-4" id="searchCEBar" placeholder="Search...">
                </div>

                <div class="col-12 col-md-3 position-relative">
                    <label class="form-label">Status</label>

                    <!-- Dropdown toggle button -->
                    <button class="btn btn-outline-secondary w-100 text-start rounded-4" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Select Status
                    </button>

                    <!-- Dropdown menu with checkboxes -->
                    <ul class="dropdown-menu p-2" style="max-height: 250px; overflow-y: auto;">
                        <li><label><input type="checkbox" class="status-checkbox" value=""> ***All Status***</label></li>
                        <li><label><input type="checkbox" class="status-checkbox" value="PENDING"> PENDING</label></li>
                        <li><label><input type="checkbox" class="status-checkbox" value="APPROVED"> APPROVED</label></li>
                        <li><label><input type="checkbox" class="status-checkbox" value="CANCELLED"> CANCELLED</label></li>
                        <li><label><input type="checkbox" class="status-checkbox" value="DECLINED"> DECLINED</label></li>
                    </ul>
                </div>

                <div class="col-2">
                    <label for="rowsPerPage" class="form-label rounded-4">Rows per page</label>
                    <select id="rowsPerPage" class="form-select rounded-4">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                        <option value="30">30</option>
                        <option value="40">40</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

            <label for="dateyeardropdown" class="form-label rounded-4">Date Filter</label>

            <div class="col-4 d-flex align-items-center gap-2 mb-4">

                <select class="form-select rounded-4" id="monthDropdown" onchange="onMonthYearChange()">
                </select>

                <select class="form-select rounded-4" id="yearDropdown" onchange="onMonthYearChange()">
                </select>
            </div>

            <div id="add-button-container" class="d-none">
                <button type="button" class="btn btn-primary rounded-4 mb-4 mt-2" data-bs-toggle="modal" data-bs-target="#TaggingModal">Add Approval Flow</button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm small"
                    id="wtm_shiftschedule_employeelist"
                    style="font-size: 0.875rem;">
                    <thead class="thead-light mb-4">
                        <tr class="bg-dark text-white text-center rounded-4">
                            <th class="bg-primary text-white">Request ID</th>
                            <th class="bg-primary text-white">Employee ID</th>
                            <th class="bg-primary text-white">Employee Name</th>
                            <th class="bg-primary text-white">Department</th>
                            <th class="bg-primary text-white">Area</th>
                            <th class="bg-primary text-white">Position</th>
                            <th class="bg-primary text-white">DTR Date</th>
                            <th class="bg-primary text-white">Date & Time Requested</th>
                            <th class="bg-primary text-white">Status</th>
                            <th class="bg-primary text-white">Action</th>
                        </tr>
                    </thead>
                    <tbody id="employees_tbody"></tbody>
                </table>

                <!-- Display of current entries shown -->
                <div class="container mb-3">
                    <p class="text-center">
                        Showing <span id="startCount"></span> to <span id="endCount"></span> of <span id="totalEntries"></span> entries
                    </p>

                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center" id="pagination">
                            <!-- Pagination buttons will be dynamically generated here -->
                        </ul>
                    </nav>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include '../includes/footer_upper.php'; ?>
<script src="../assets/js/wtm/wtm_manual_attendance_validation_list.js"></script>
<script>
    //====== LIST OF MONTHS AND YEAR ====== //

    function loadMonthYearDropdowns(selectedMonth, selectedYear) {
        // === Generate Months ===
        const monthDropdown = document.getElementById("monthDropdown");
        monthDropdown.innerHTML = ""; // clear previous options
        const months = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];
        const currentMonth = new Date().getMonth(); // 0 = January

        months.forEach((m, i) => {
            const opt = document.createElement("option");
            opt.value = i + 1; // Month number: January = 1, February = 2, ...
            opt.textContent = m;
            // Select either the passed month or current month
            if ((selectedMonth && i + 1 === selectedMonth) || (!selectedMonth && i === currentMonth)) {
                opt.selected = true;
            }
            monthDropdown.appendChild(opt);
        });

        // === Generate Years ===
        const yearDropdown = document.getElementById("yearDropdown");
        yearDropdown.innerHTML = ""; // clear previous options
        const currentYear = new Date().getFullYear();
        const range = 5; // +/- 5 years around current

        for (let y = currentYear - range; y <= currentYear + range; y++) {
            const opt = document.createElement("option");
            opt.value = y; // year as number
            opt.textContent = y;
            // Select either the passed year or current year
            if ((selectedYear && y === selectedYear) || (!selectedYear && y === currentYear)) {
                opt.selected = true;
            }
            yearDropdown.appendChild(opt);
        }
    }


    loadMonthYearDropdowns();
</script>
<?php include '../includes/footer_lower.php'; ?>