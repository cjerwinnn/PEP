function fetchChecklistAndUpdateTable() {
    fetch('fetch/maintenance/maintenance_coe_checklist.php')
        .then(res => res.json())
        .then(data => {
            const tbody = document.querySelector('#maintenance_checklist_table tbody');
            tbody.innerHTML = '';

            data.forEach(row => {
                const required = row.checklist_required == '1';
                const toggleId = 'required_switch_' + Math.random().toString(36).substr(2, 9);

                const tr = document.createElement('tr');
                tr.innerHTML = `
                <td class="text-start text-wrap">${escapeHtml(row.coe_type)}</td>
                <td class="text-start text-wrap">${escapeHtml(row.requirements_name)}</td>
                <td class="text-start text-wrap">${escapeHtml(row.requirements_description)}</td>
                <td class="text-center">
                    <div class="form-check form-switch d-flex justify-content-center">
                        <input class="form-check-input" type="checkbox" role="switch" id="${toggleId}" ${required ? 'checked' : ''}>
                    </div>
                </td>
            `;
                tbody.appendChild(tr);
                maintenance_coechecklist_filter();
            });
        });
}

function InsertCOERequirements(btn) {

    const coeTypeEl = document.getElementById('add_checklist_coe_type');
    const coeType = coeTypeEl.value;

    const reqNameEl = document.getElementById('requirement_name');
    const reqName = reqNameEl.value.trim();

    const reqDescEl = document.getElementById('req_reason');
    const reqDesc = reqDescEl.value.trim();

    const required = document.getElementById('flexSwitchCheckDefault').checked;

    if (!coeType) {
        showAlert('Please select COE Type');
        coeTypeEl.focus();
        return;
    }

    if (!reqName) {
        showAlert('Please enter requirement name');
        reqNameEl.focus();
        return;
    }

    if (!reqDesc) {
        showAlert('Please enter requirement description');
        reqDescEl.focus();
        return;
    }

    btn.disabled = true; // disable button while processing

    fetch('inserts/maintenance_checklist_insert.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            coe_type: coeType,
            requirements_name: reqName,
            requirements_description: reqDesc,
            checklist_required: required
        })
    })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            if (data.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('TaggingModal'));
                modal.hide();

                // Clear inputs
                document.getElementById('add_checklist_coe_type').value = '';
                document.getElementById('requirement_name').value = '';
                document.getElementById('req_reason').value = '';
                document.getElementById('flexSwitchCheckDefault').checked = true;

                // Refresh table
                fetchChecklistAndUpdateTable();
            } else {
                showAlert('Error inserting data: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(err => {
            btn.disabled = false;
            showAlert('Request failed: ' + err.message);
        });
}
