function escapeHtml(text) {
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

let currentPage = 1;
let pageLimit = 10;

function searchCETable() {
    currentPage = 1;
    applyFiltersAndPaginate();
}

function paginateTable() {
    applyFiltersAndPaginate();
}

function applyFiltersAndPaginate() {
    const input = document.getElementById("ceSearchBar");
    const filter = input.value.toLowerCase();
    const table = document.getElementById("maintenance_checklist_table");
    const tbody = table.querySelector("tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));
    const pageLimitSelect = document.getElementById("maintenance_checklist_tableLimit");
    pageLimit = parseInt(pageLimitSelect.value);

    const filteredRows = rows.filter(row => {
        const cells = row.getElementsByTagName("td");
        for (let i = 0; i < cells.length; i++) {
            const cellText = cells[i].textContent || cells[i].innerText;
            if (cellText.toLowerCase().includes(filter)) return true;
        }
        return false;
    });

    const totalRows = filteredRows.length;
    const totalPages = Math.max(1, Math.ceil(totalRows / pageLimit));

    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    rows.forEach(row => row.style.display = "none");
    const start = (currentPage - 1) * pageLimit;
    const end = start + pageLimit;
    filteredRows.slice(start, end).forEach(row => row.style.display = "");

    document.getElementById("cePageInfo").textContent = `Page ${currentPage} of ${totalPages}`;
}

function nextPage() {
    currentPage++;
    applyFiltersAndPaginate();
}

function prevPage() {
    currentPage--;
    applyFiltersAndPaginate();
}

function firstPage() {
    currentPage = 1;
    applyFiltersAndPaginate();
}

function lastPage() {
    const input = document.getElementById("ceSearchBar");
    const filter = input.value.toLowerCase();
    const table = document.getElementById("ceTable");
    const rows = Array.from(table.querySelector("tbody").querySelectorAll("tr"));
    const filteredRows = rows.filter(row => {
        const cells = row.getElementsByTagName("td");
        for (let i = 0; i < cells.length; i++) {
            const cellText = cells[i].textContent || cells[i].innerText;
            if (cellText.toLowerCase().includes(filter)) return true;
        }
        return false;
    });

    const totalPages = Math.max(1, Math.ceil(filteredRows.length / pageLimit));
    currentPage = totalPages;
    applyFiltersAndPaginate();
}

function maintenance_coechecklist_filter() {
    const input = document.getElementById('ceSearchBar').value.toLowerCase();
    const filter = document.getElementById('ceFilterDropdown').value.toLowerCase();
    const table = document.getElementById('maintenance_checklist_table');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    let visibleCount = 0;
    const limit = parseInt(document.getElementById('maintenance_checklist_tableLimit').value);
    const pageInfo = document.getElementById('cePageInfo');

    for (let i = 0; i < rows.length; i++) {
        const cols = rows[i].getElementsByTagName('td');
        const rowText = rows[i].innerText.toLowerCase();
        const coeType = cols[0].innerText.toLowerCase();

        const matchesSearch = rowText.includes(input);
        const matchesFilter = filter === "" || coeType.includes(filter);

        if (matchesSearch && matchesFilter) {
            rows[i].style.display = visibleCount < limit ? "" : "none";
            visibleCount++;
        } else {
            rows[i].style.display = "none";
        }
    }

    pageInfo.innerText = `Showing ${Math.min(visibleCount, limit)} of ${visibleCount} entries`;
}

//MAINTENANCE COE APPROVAL FLOW

let selectedOrder = [];


function loadDepartments() {
    document.getElementById('add-button-container').classList.add('d-none');

    const deptSelect = document.getElementById('departmentFilterDropdown');
    deptSelect.innerHTML = '<option value="">Loading departments...</option>';
    const areaSelect = document.getElementById('areaFilterDropdown');
    areaSelect.innerHTML = '<option value="">Select Department first</option>';
    areaSelect.disabled = true;

    fetch('fetch/maintenance/maintenance_ref_department.php')
        .then(response => response.text())
        .then(options => {
            deptSelect.innerHTML = options;
        })
        .catch(error => {
            deptSelect.innerHTML = '<option value="">Failed to load departments</option>';
            console.error('Error loading departments:', error);
        });
}

function loadAreas(department) {
    document.getElementById('add-button-container').classList.add('d-none');

    const areaSelect = document.getElementById('areaFilterDropdown');
    areaSelect.innerHTML = '<option value="">Loading areas...</option>';
    areaSelect.disabled = true;

    if (!department) {
        areaSelect.innerHTML = '<option value="">Select Department first</option>';
        areaSelect.disabled = true;
        return;
    }

    const formData = new FormData();
    formData.append('department', department);

    fetch('fetch/maintenance/maintenance_ref_area.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(options => {
            areaSelect.innerHTML = options;
            areaSelect.disabled = false;
        })
        .catch(error => {
            areaSelect.innerHTML = '<option value="">Failed to load areas</option>';
            console.error('Error loading areas:', error);
        });
}

function onDepartmentChange() {
    const selectedDept = document.getElementById('departmentFilterDropdown').value;
    loadAreas(selectedDept);
}

function onAreaChange() {
    const selectedArea = document.getElementById('areaFilterDropdown').value;
    LoadApprovalFlow(selectedArea);
}


function LoadApprovalFlow(area) {
    const tbody = document.querySelector('#maintenance_approvalflow_table tbody');
    tbody.innerHTML = '<tr><td colspan="7" class="text-center">Loading...</td></tr>';

    const formData = new FormData();
    formData.append('area', area);

    fetch('fetch/maintenance/maintenance_coe_approvalflow.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.text())
        .then(data => {
            tbody.innerHTML = data || '<tr><td colspan="7" class="text-center">No data found</td></tr>';
            document.getElementById('form_approvalform_area').value = area;
            const rows = tbody.querySelectorAll('tr');
            if (rows.length > 0) {
                const lastRow = rows[rows.length - 1];
                const levelCell = lastRow.querySelector('td');

                if (levelCell) {
                    const currentLevel = parseInt(levelCell.textContent.trim()) || 0;
                    const nextLevel = currentLevel;
                    document.getElementById('form_approvalform_approvallevel').value = nextLevel;
                    document.getElementById('add-button-container').classList.remove('d-none');
                    resetModalFully('TaggingModal');
                }
            } else {
                document.getElementById('form_approvalform_approvallevel').value = 0;
                document.getElementById('add-button-container').classList.remove('d-none');
                resetModalFully('TaggingModal');
            }

        })
        .catch(err => {
            console.error('Error fetching approval flow:', err);
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading data</td></tr>';
        });
}

function resetModalFully(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.addEventListener('hidden.bs.modal', () => {
        // Reset form fields
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
        }

        modal.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(input => {
            input.checked = false;
        });

        modal.querySelectorAll('textarea').forEach(textarea => {
            textarea.value = '';
        });

        modal.querySelectorAll("[id^='level_']").forEach(el => {
            el.textContent = '';
        });

        // ✅ Reset the selectedOrder array
        selectedOrder = [];
    });
}

function setupApprovalLevelAssignment() {
    const levelCheckboxes = document.querySelectorAll('.level-checkbox');
    const currentApprovalLevelInput = document.getElementById('form_approvalform_approvallevel');

    if (!currentApprovalLevelInput) {
        console.warn('Current approval level input not found.');
        return;
    }

    levelCheckboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            const employeeId = this.dataset.employeeid;
            const currentApprovalLevel = parseInt(currentApprovalLevelInput.value, 10) || 0;

            if (this.checked) {
                if (!selectedOrder.includes(employeeId)) {
                    selectedOrder.push(employeeId);
                }
            } else {
                selectedOrder = selectedOrder.filter(id => id !== employeeId);
            }

            // Enable/disable override switch
            const overrideSwitch = document.getElementById('override_' + employeeId);
            if (overrideSwitch) {
                overrideSwitch.disabled = !this.checked;
            }

            // Update level display
            selectedOrder.forEach((id, index) => {
                const level = currentApprovalLevel + index + 1;
                const levelCell = document.getElementById('level_' + id);
                if (levelCell) {
                    levelCell.innerText = level;
                    console.log(`Employee ${id} assigned to approval level ${level}`);
                }
            });

            // Clear level for unchecked employees
            document.querySelectorAll('.level-checkbox').forEach(c => {
                const eid = c.dataset.employeeid;
                if (!selectedOrder.includes(eid)) {
                    const levelCell = document.getElementById('level_' + eid);
                    if (levelCell) levelCell.innerText = '';
                }
            });
        });
    });

}

function Approvalflow_Search(searchInputId, tbodyId) {
    const input = document.getElementById(searchInputId);
    const tbody = document.getElementById(tbodyId);
    if (!input || !tbody) return;

    input.addEventListener('input', function () {
        const filter = input.value.toLowerCase();
        const rows = tbody.querySelectorAll('tr');

        rows.forEach(row => {
            const cellsText = Array.from(row.cells).map(td => {
                let text = td.textContent.toLowerCase();
                const labels = td.querySelectorAll('input, label');
                if (labels.length > 0) {
                }
                return text;
            }).join(' ');

            row.style.display = cellsText.includes(filter) ? 'table-row' : 'none';
        });
    });
}


function collectAndSubmitApprovers(moduleName, phpInsertUrl) {
    const area = document.getElementById('form_approvalform_area').value;

    const approvers = [];

    document.querySelectorAll('.level-checkbox:checked').forEach(checkbox => {
        const employeeId = checkbox.dataset.employeeid;

        // Get approval level from table cell
        const levelCell = document.getElementById('level_' + employeeId);
        const approvalLevel = levelCell ? parseInt(levelCell.innerText) || 0 : 0;

        // Get override switch checked and enabled status
        const overrideSwitch = document.getElementById('override_' + employeeId);
        const overrideAccess = overrideSwitch && !overrideSwitch.disabled && overrideSwitch.checked ? 1 : 0;

        approvers.push({
            employeeid: employeeId,
            approver_level: approvalLevel,
            override_access: overrideAccess
        });
    });

    if (approvers.length === 0) {
        showAlert('Please select at least one approver.');
        return;
    }

    // Prepare form data
    const formData = new FormData();
    formData.append('area', area);
    formData.append('module', moduleName);
    formData.append('approvers', JSON.stringify(approvers));

    fetch(phpInsertUrl, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const taggingModalEl = document.getElementById('TaggingModal');
                const modal = bootstrap.Modal.getInstance(taggingModalEl);
                if (modal) modal.hide();
                LoadApprovalFlow(area)
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An unexpected error occurred.');
        });
}