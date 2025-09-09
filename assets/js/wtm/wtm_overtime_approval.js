//=====GLOBAL=====//

const employeeid = document.querySelector('#employeeId').value;

//===END GLOBAL==//

// ======= Textarea Auto-Resize =======
function adjustTextareaHeight(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = textarea.scrollHeight + 'px';
}


function showAlert(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
    <strong>Warning!</strong> ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  `;

    const alertPlaceholder = document.getElementById('alert-placeholder');
    if (alertPlaceholder) {
        alertPlaceholder.append(alertDiv);

        setTimeout(() => {
            // Bootstrap's fade out and remove element
            alertDiv.classList.remove('show');
            alertDiv.classList.add('fade'); // Keep fade for transition
            alertDiv.classList.add('hide');
            setTimeout(() => {
                alertDiv.remove();
            }, 200); // Slightly longer for smooth transition
        }, 2500);
    }
}

function showSuccess(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        <strong>Success!</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
    document.getElementById('alert-placeholder').append(alertDiv);

    setTimeout(() => {
        // Bootstrap's fade out and remove element
        alertDiv.classList.remove('show');
        alertDiv.classList.add('hide');
        // Remove from DOM after fade out transition (usually 150ms)
        setTimeout(() => {
            alertDiv.remove();
        }, 150);
    }, 2000);
}

document.addEventListener('DOMContentLoaded', () => {
    const dateInput = document.getElementById('ot-approved');
    const minDate = dateInput.min; // from min attribute
    const maxDate = dateInput.max; // from max attribute

    dateInput.addEventListener('input', () => {
        if (dateInput.value < minDate) {
            dateInput.value = minDate; // force to min
        } else if (dateInput.value > maxDate) {
            dateInput.value = maxDate; // force to max
        }
    });
});

function Fetch_ApprovalFlow(buttonId) {
    const btn = document.getElementById(buttonId);
    if (btn) {
        btn.addEventListener("click", function () {

            let approver_level = 0;
            let isApproved = '';

            const empArea = document.getElementById('area_data').value;
            const overtimeId = document.getElementById('overtimeid_data').value;

            const formData = new FormData();
            formData.append('requestor_area', empArea);
            formData.append('overtime_id', overtimeId);

            fetch('../fetch/wtm/wtm_ot_approvalflow_fetch.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    const list = document.getElementById('approvalFlowList');
                    list.innerHTML = '';

                    data.forEach(item => {
                        const li = document.createElement('li');

                        const currentUserId = document.getElementById('current_user').value;
                        const approver_id = item.approver_employeeid;
                        const isCurrentUser = approver_id == currentUserId;

                        if (isCurrentUser) {
                            approver_level = item.approver_level;
                            isApproved = item.tagged_status;
                        }


                        const borderClass = isCurrentUser ? 'bg-light border border-info border-2' : 'border-1';

                        li.className = `list-group-item p-4 rounded-4 shadow-sm mb-3 ${borderClass}`;

                        // Format tagged datetime
                        const hasTagged = item.tagged_date && item.tagged_time;
                        const formattedDateTime = hasTagged
                            ? `<div class="text-muted small"><i class="bi bi-calendar-check"></i> ${item.tagged_date} ${item.tagged_time}</div>`
                            : `<div class="text-muted small fst-italic"></div>`;

                        // Approver department, area, position
                        const approverDetails = `
                        <div class="text-secondary small mb-1">
                            <i class="bi bi-building"></i> ${item.department || '—'} |
                            <i class="bi bi-geo-alt"></i> ${item.area || '—'} |
                            <i class="bi bi-person-badge"></i> ${item.position || '—'}
                        </div>
                        `;

                        // Remarks
                        const remarks = item.tagged_remarks
                            ? `<div class="small text-muted fst-italic mb-2 mt-2">Remarks: ${item.tagged_remarks}</div>`
                            : '';

                        // Status badge with colors
                        const statusClass = item.tagged_status === 'APPROVED' ? 'bg-success' :
                            item.tagged_status === 'DENIED' ? 'bg-danger' :
                                item.tagged_status === 'PENDING' ? 'bg-warning text-dark' :
                                    'bg-secondary';

                        li.innerHTML = `
                            <div class="d-flex justify-content-between align-items-start flex-wrap">
                            <div class="me-3">
                                <h6 class="mb-1">
                                Level ${item.approver_level} — 
                                <span class="fw-semibold">${item.employee}</span>
                                </h6>
                                ${approverDetails}
                                <span class="mt-2">${remarks}</span>
                                ${formattedDateTime}
                            </div>
                            <span class="badge rounded-pill px-3 py-2 fs-10 mt-2 ${statusClass}">
                                ${item.tagged_status}
                            </span>
                            </div>
                        `;

                        list.appendChild(li);
                    });

                    checkLowerLevelApprovals(approver_level, isApproved)

                    document.getElementById('approver_level').value = approver_level;

                })
                .catch(error => {
                    console.error('Error fetching approval flow:', error);
                });

        });
    }
}

function checkLowerLevelApprovals(approverlevel, isApproved) {

    const formData = new FormData();
    const overtimeId = document.getElementById('overtimeid_data').value;

    formData.append('request_id', overtimeId);
    formData.append('current_level', approverlevel);

    fetch('../fetch/wtm/wtm_overtime_checkapprovals.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.text())
        .then(response => {
            console.log('Response:', response);
            if (response.trim() === 'PROCEED') {
                if (isApproved == 'APPROVED') {
                    document.getElementById('approver-access').classList.add('d-none');
                    document.getElementById('waiting-approval-access').innerHTML = '';
                } else {
                    document.getElementById('approver-access').classList.remove('d-none');
                    document.getElementById('waiting-approval-access').innerHTML = '';
                }
            } else {
                document.getElementById('approver-access').classList.add('d-none');
                document.getElementById('waiting-approval-access').innerHTML = 'Waiting for prior approvals...';
            }
        })
        .catch(error => console.error('Error:', error));
}

// Approved

document.getElementById('approved_request').addEventListener("click", function (event) {
    event.preventDefault(); // prevent form auto-submit

    const ApprovedOT = document.getElementById('ot-approved');
    const approval_remarks = document.getElementById('approval_remarks');

    if (ApprovedOT.value === '' || parseFloat(ApprovedOT.value) <= 0) {
        showAlert('Please provide approved overtime hour(s).');
        ApprovedOT.focus();
        this.disabled = false;
        this.textContent = 'Approve application';
        return; // modal stays open
    }

    if (!approval_remarks.value) {
        showAlert('Please provide a approval remarks for the overtime application approval.');
        approval_remarks.focus();
        this.disabled = false;
        this.textContent = 'Approve application';
        return;
    }


    const formData = new FormData();
    // Header Data
    const overtimeId = document.getElementById('overtimeid_data').value;
    const currentUserId = document.getElementById('current_user').value;
    const requestor_EmployeeID = document.getElementById('requestorempid_data').value;
    const approved_overtime_hours = document.getElementById('ot-approved').value;
    const approver_level = document.getElementById('approver_level').value;

    formData.append('request_id', overtimeId);
    formData.append('requestor_EmployeeID', requestor_EmployeeID);
    formData.append('approved_overtime_hours', approved_overtime_hours);
    formData.append('tagged_by', currentUserId);
    formData.append('request_status', 'APPROVED');
    formData.append('approver_level', approver_level);
    formData.append('approval_remarks', $('#approval_remarks').val());

    $.ajax({
        url: '../updates/wtm/wtm_overtime_approval_update.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            $('#yes-btn').prop('disabled', true);
            $('#no-btn').prop('disabled', true);
            $('#alert-placeholder').empty();
        },
        success: function (response) {
            Swal.fire({
                title: "Approved!",
                text: "Request Approved.",
                icon: "success",
                confirmButtonText: "OK",
                confirmButtonColor: "#198754",
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                if (result.isConfirmed) {
                    const page = 'wtm_overtime_approval_list.php';
                    window.location.href = page;
                }
            });
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
            showAlert('Error submitting request.');
            $('#yes-btn').prop('disabled', false);
            $('#no-btn').prop('disabled', false);
        }
    });
});


// Declined

document.getElementById('declined_request').addEventListener("click", function (event) {
    event.preventDefault(); // prevent form auto-submit

    const approval_remarks = document.getElementById('approval_remarks');

    if (!approval_remarks.value) {
        showAlert('Declined remarks is required.');
        approval_remarks.focus();
        this.disabled = false;
        this.textContent = 'Decline application';
        return;
    }

    const formData = new FormData();
    // Header Data
    const overtimeId = document.getElementById('overtimeid_data').value;
    const currentUserId = document.getElementById('current_user').value;
    const requestor_EmployeeID = document.getElementById('requestorempid_data').value;
    const approver_level = document.getElementById('approver_level').value;

    formData.append('request_id', overtimeId);
    formData.append('requestor_EmployeeID', requestor_EmployeeID);
    formData.append('approved_overtime_hours', 0);
    formData.append('tagged_by', currentUserId);
    formData.append('request_status', 'DECLINED');
    formData.append('approver_level', approver_level);
    formData.append('approval_remarks', $('#approval_remarks').val());

    $.ajax({
        url: '../updates/wtm/wtm_overtime_decline_update.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            $('#yes-btn').prop('disabled', true);
            $('#no-btn').prop('disabled', true);
            $('#alert-placeholder').empty();
        },
        success: function (response) {
            Swal.fire({
                title: "Declined!",
                text: "Request Declined.",
                icon: "success",
                confirmButtonText: "OK",
                confirmButtonColor: "#ff1e00ff",
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                if (result.isConfirmed) {
                    const page = 'wtm_overtime_approval_list.php';
                    window.location.href = page;
                }
            });
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
            showAlert('Error submitting request.');
            $('#yes-btn').prop('disabled', false);
            $('#no-btn').prop('disabled', false);
        }
    });
});
