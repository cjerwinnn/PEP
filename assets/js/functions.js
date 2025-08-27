function loadDepartments() {
    document.getElementById('add-button-container').classList.add('d-none');

    const deptSelect = document.getElementById('departmentFilterDropdown');
    deptSelect.innerHTML = '<option value="">Loading departments...</option>';
    const areaSelect = document.getElementById('areaFilterDropdown');
    areaSelect.innerHTML = '<option value="">Select Department first</option>';
    areaSelect.disabled = true;

    fetch('../fetch/maintenance/maintenance_ref_department.php')
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

    fetch('../fetch/maintenance/maintenance_ref_area.php', {
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