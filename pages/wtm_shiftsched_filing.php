<?php
include '../includes/header.php';
?>

<input type="hidden" id="department_data" value="ADMIN SERVICES">
<input type="hidden" id="area_data" value="IT">

<div class="container-fluid mb-2">
    <div class="card shadow rounded-4">
        <div class="card-body p-4">

            <div class="d-flex align-items-center mb-3">
                <a href="wtm_submenu.php" class="btn btn-outline-secondary btn-sm rounded-4">
                    ← Back
                </a>
                <h5 class="mb-0 ms-3 text-muted text-center">Shift Schedule Request</h5>
            </div>

            <div class="row">

                <div class="col-12 col-md-3 d-flex align-items-center gap-2 mb-2">
                    <select class="form-select rounded-4" id="monthDropdown" onchange="onMonthYearChange()">
                    </select>

                    <select class="form-select rounded-4" id="yearDropdown" onchange="onMonthYearChange()">
                    </select>
                </div>


                <div class="col-12 col-md-5 d-flex align-items-center gap-2 mb-2">
                    <input type="text" class="form-control rounded-4" id="ceSearchBar" placeholder="Search..." onkeyup="maintenance_coechecklist_filter()">
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-2">
                    <label for="maintenance_checklist_tableLimit" class="form-label mb-0">Show</label>
                    <select id="maintenance_checklist_tableLimit" class="form-select form-select-sm w-auto rounded-4" onchange="paginateTable()">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                    </select>
                    <label class="form-label mb-0">entries</label>
                </div>
                <div id="cePageInfo" class="small text-muted"></div>
            </div>

            <a href="wtm_mydtr.php" type="button" class="btn btn-primary rounded-4 mb-4">
                Request Change Shift
            </a>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm small"
                    id="wtm_shiftschedule_employeelist"
                    style="table-layout: fixed; width: 100%; font-size: 0.875rem;">
                    <thead>
                        <!-- Main Month Header -->
                        <tr>
                            <th colspan="10" class="text-center fs-4 fw-bold bg-light" id="monthYearHeader">
                            </th>
                        </tr>

                        <!-- From / To grouping -->
                        <tr class="text-center">
                            <th rowspan="2" class="bg-primary text-white fw-bold" style="width:40%;">Request ID</th>
                            <th rowspan="2" class="bg-primary text-white fw-bold">Shift Date</th>
                            <th colspan="2" class="bg-primary text-white fw-bold">FROM</th>
                            <th colspan="2" class="bg-primary text-white fw-bold">TO</th>
                            <th rowspan="2" class="bg-primary text-white fw-bold">Status</th>
                            <th rowspan="2" class="bg-primary text-white fw-bold">Date Created</th>
                            <th rowspan="2" class="bg-primary text-white fw-bold">Time Created</th>
                            <th rowspan="2" class="bg-primary text-white fw-bold">Action</th>
                        </tr>

                        <!-- Sub-headers for FROM / TO -->
                        <tr class="text-center">
                            <th class="bg-primary text-white">Shift Code</th>
                            <th class="bg-primary text-white">Schedule</th>
                            <th class="bg-primary text-white">Shift Code</th>
                            <th class="bg-primary text-white">Schedule</th>
                        </tr>
                    </thead>

                    <tbody id="employees_tbody"></tbody>
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

<?php include '../includes/footer_upper.php'; ?>
<script src="../assets/js/functions.js"></script>
<script src="../assets/js/wtm/wtm_shiftsched_filing.js"></script>
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