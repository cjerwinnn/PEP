//=====GLOBAL=====//

const employeeid = document.querySelector('#employeeId').value;
let selected_date = '';
let current_shiftcode = '';
let current_shiftstart = '';
let current_shiftend = '';

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



function FetchEmployeeDetails(employeeId) {
    if (!employeeId) return;

    $.ajax({
        url: '../fetch/wtm/wtm_employee_details.php',
        type: 'POST',
        data: { employee_id: employeeId },
        dataType: 'json',
        success: function (response) {
            if (response.error) {
                console.error(response.error);
                return;
            }

            $('#modal-employee-info').text('[' + response.employeeid + '] ' + response.employeename);
            $('#modal-area').text(response.area);
            $('#modal-department').text(response.department);
            $('#modal-position').text(response.position);
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
        }
    });
}


FetchEmployeeDetails(employeeid);

function loadCutoffDropdown(dropdownId = 'cutoffDropdown', url = '../fetch/wtm/wtm_cutoff_fetch.php') {
    const dropdown = document.getElementById(dropdownId);
    if (!dropdown) {
        console.error(`Dropdown element with ID "${dropdownId}" not found.`);
        return;
    }

    fetch(url)
        .then(response => response.text())
        .then(data => {
            dropdown.innerHTML = data;
        })
        .catch(error => {
            console.error('Error loading cutoff dropdown:', error);
        });
}

loadCutoffDropdown();

function cutoffChanged() {
    const dropdown = document.getElementById('cutoffDropdown');
    const selectedOption = dropdown.options[dropdown.selectedIndex];

    const cutoff = selectedOption.value;
    const coFrom = selectedOption.getAttribute('data-co-from');
    const coTo = selectedOption.getAttribute('data-co-to');

    console.log('Selected cutoff:', cutoff);
    console.log('Cutoff date from:', coFrom);
    console.log('Cutoff date to:', coTo);

    loadAttendance(employeeid, coFrom, coTo)
}

function loadAttendance(employeeId, startDate, endDate) {
    const start = startDate;
    const end = endDate;

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        return isNaN(d.getTime()) ? '' : d.toLocaleDateString('en-US', {
            month: 'short',
            day: '2-digit',
            year: 'numeric'
        });
    }

    const attendanceTableBody = document.querySelector('#attendanceTable tbody');
    attendanceTableBody.innerHTML = '<tr><td colspan="16" class="text-center">Loading Daily Time Record for ' + formatDate(startDate) + ' to ' + formatDate(endDate) + '.</td></tr>';

    fetch(`../fetch/wtm/wtm_mydtr_fetch.php?employeeid=${employeeId}&start=${start}&end=${end}`, {
        cache: 'no-store'
    })

        .then(response => {
            if (!response.ok) throw new Error('Failed to fetch attendance');
            return response.json();
        })
        .then(data => {
            attendanceTableBody.innerHTML = '';

            if (!Array.isArray(data) || data.length === 0) {
                attendanceTableBody.innerHTML = '<tr><td colspan="16" class="text-center">No attendance data found.</td></tr>';
                return;
            }

            data.forEach(record => {
                attendanceTableBody.innerHTML += `
                <tr>
                    <td class="text-center small">${record.date ? new Date(record.date).toLocaleDateString('en-US', {
                    month: 'short',
                    day: '2-digit',
                    year: 'numeric'
                }) : ''
                    }</td>                    
                    <td class="text-center small">${record.shiftcode || ''}</td>
                    <td class="text-center small">${record.dayofweek || ''}</td>

                    ${record.shiftin === '00:00:00' ?
                        '<td class="text-center text-danger"></td>' :
                        `<td class="text-center small">${(record.shiftin || '').slice(0, 5)}</td>`}
                    ${record.shiftout === '00:00:00' ?
                        '<td class="text-center text-danger"></td>' :
                        `<td class="text-center small">${(record.shiftout || '').slice(0, 5)}</td>`}

                    <td class="text-center">${record.datein || ''}</td>
                    <td class="text-center">
                    ${record.timein === '00:00:00' || record.shiftin === '00:00:00'
                        ? '<span class="text-danger small">No Time In</span>'
                        : record.timein.slice(0, 5)}
                    </td>
                    <td class="text-center small">${record.dateout || ''}</td>
                    <td class="text-center">
                    ${record.timeout === '00:00:00' || record.shiftin === '00:00:00'
                        ? '<span class="text-danger small">No Time Out</span>'
                        : record.timeout.slice(0, 5)}
                    </td>

                    ${record.tardiness === '0' ?
                        '<td class="text-center text-success"></td>' :
                        `<td class="text-center text-danger small">${record.tardiness || '0'}</td>`}

                    ${record.undertime === '0' ?
                        '<td class="text-center text-success"></td>' :
                        `<td class="text-center text-danger small">${record.undertime || '0'}</td>`}

                    ${record.totalmanhours === '0' || record.totalmanhours === '0.0' ?
                        '<td class="text-center text-success"></td>' :
                        `<td class="text-center text-danger small">${record.totalmanhours || '0'}</td>`}

                    ${record.nightdiff === '0' || record.nightdiff === '0.0' ?
                        '<td class="text-center text-success"></td>' :
                        `<td class="text-center text-danger small">${record.nightdiff || '0'}</td>`}

                    ${record.overtime === '0' ?
                        '<td class="text-center text-success"></td>' :
                        `<td class="text-center text-danger small">${record.overtime || '0'}</td>`}

                            <td class="text-center">
                            <button 
                            class="btn btn-sm btn-primary rounded-4"
                            data-bs-toggle="modal"
                            data-bs-target="#DTRDetailModal"
                            data-employeeid="${employeeId}"
                            data-date="${record.date}">
                            <ion-icon name="document-text-outline"></ion-icon>
                            </button>
                        </td>


                    <td class="text-center">
                    ${record.remarks
                        ? `<i class="bi bi-info-circle-fill text-primary" data-bs-toggle="tooltip" title="${record.remarks}"></i>`
                        : ''}
                    </td>

                </tr>
            `;
            });
        })
        .catch(error => {
            attendanceTableBody.innerHTML = '<tr><td colspan="16" class="text-center text-danger">Failed to load attendance.</td></tr>';
            console.error(error);
        });
}

function initializeFormModal(modalId = 'DTRDetailModal') {
    const formModal = document.getElementById(modalId);
    if (!formModal) return;

    formModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        if (!button) return;

        selected_date = button.getAttribute('data-date');

        if (!employeeid || !selected_date) {
            console.error("Missing employeeId or date");
            return;
        }

        $.ajax({
            url: '../fetch/wtm/wtm_dtr_details.php',
            type: 'POST',
            data: { employee_id: employeeid, date: selected_date },
            dataType: 'json',
            success: function (data) {
                if (data.error) {
                    console.error(data.error);
                    return;
                }

                function formatDate(dateStr) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);
                    return isNaN(d.getTime()) ? '' : d.toLocaleDateString('en-US', {
                        month: 'short',
                        day: '2-digit',
                        year: 'numeric'
                    });
                }

                const formatTime = (timeStr) => {
                    if (!timeStr || timeStr === '00:00:00') return '';
                    return timeStr.slice(0, 5);
                };

                // Fill modal with DB values
                const dateFormatted = formatDate(selected_date);
                formModal.querySelector('#modal-date-value').textContent = dateFormatted;


                // Shift
                let shiftDisplay = (data.shiftin === '00:00:00' && data.shiftout === '00:00:00')
                    ? `<strong>[${data.shiftcode}]</strong>`
                    : `<strong>[${data.shiftcode}] ${formatTime(data.shiftin)} - ${formatTime(data.shiftout)}</strong>`;

                fetch(`../fetch/wtm/wtm_check_changeshift_request.php?employeeid=${employeeid}&shiftdate=${selected_date}`)
                    .then(response => response.json())
                    .then(result => {
                        if (parseInt(result.total) === 0) {

                            shiftDisplay += `
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary rounded-4 ms-2" 
                                        id="btnChangeShift" 
                                        onclick="ChangeShift('${employeeid}', '${selected_date}')"
                                        Change Shift Request
                                </button>`;
                        } else {
                            shiftDisplay += `
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary rounded-4 ms-2" 
                                        id="btnChangeShift" 
                                        View Request
                                        <span class="badge bg-warning ms-2">${result.status}</span>
                                </button>`;
                        }

                        formModal.querySelector('#modal-shift').innerHTML = `Shift Schedule:  ${shiftDisplay}`;
                    })
                    .catch(err => console.error(err));

                current_shiftcode = data.shiftcode;
                current_shiftstart = data.shiftin;
                current_shiftend = data.shiftout;

                // Attendance

                // Time In
                let timeInDisplay = (data.timein === '00:00:00') ? '<strong>No Time In</strong>' : `<strong>${formatTime(data.timein)}</strong>`;
                let attendanceInHTML = (data.datein || data.timein)
                    ? `<strong>${formatDate(data.datein)}</strong> ${timeInDisplay}` : '';
                formModal.querySelector('#modal-in').innerHTML = attendanceInHTML;

                // Time Out
                let timeOutDisplay = (data.timeout === '00:00:00') ? '<strong>No Time Out</strong>' : `<strong>${formatTime(data.timeout)}</strong>`;
                let attendanceOutHTML = (data.dateout || data.timeout)
                    ? `<strong>${formatDate(data.dateout)}</strong> ${timeOutDisplay}` : '';
                formModal.querySelector('#modal-out').innerHTML = attendanceOutHTML;

                let attendanceBtn = '';

                fetch(`../fetch/wtm/wtm_check_manualio_request.php?employeeid=${employeeid}&shiftdate=${selected_date}`)
                    .then(response => response.json())
                    .then(result => {
                        if (parseInt(result.total) === 0) {

                            attendanceBtn = `
                                    <button type="button" 
                                        class="btn btn-sm btn-outline-secondary rounded-4 ms-2" 
                                        onclick="ManualRequest('${employeeid}', '${selected_date}')"
                                        id="btnRequestManualIn">
                                        Request Manual In/Out
                                    </button>
                                `;

                        } else {
                            attendanceBtn = `
                                    <button type="button" 
                                        class="btn btn-sm btn-outline-secondary rounded-4 ms-2" 
                                        onclick="ManualRequest('${employeeid}', '${selected_date}')"
                                        id="btnRequestManualIn">
                                        View Request
                                        <span class="badge bg-warning ms-2">${result.status}</span>
                                    </button>`;
                        }

                        formModal.querySelector('#modal-attendance-btn').innerHTML = attendanceBtn;
                    })
                    .catch(err => console.error(err));

                const tardinessDisplay = (data.tardiness == '0') ? '-' : `<strong>${data.tardiness} minute(s)</strong>`;
                const undertimeDisplay = (data.undertime == '0') ? '-' : `<strong>${data.undertime} minute(s)</strong>`;
                const nightdiffDisplay = (data.nightdiff == '0' || data.nightdiff == '0.0') ? '-' : `<strong>${data.nightdiff} minute(s)</strong>`;

                formModal.querySelector('#modal-tardiness').innerHTML = tardinessDisplay;
                formModal.querySelector('#modal-undertime').innerHTML = undertimeDisplay;
                formModal.querySelector('#modal-nightdiff').innerHTML = nightdiffDisplay;

                const overtimeDisplay = (data.overtime == '0') ? '-' : `<strong>${data.overtime} hour(s)</strong>`;

                let overtimeHTML = overtimeDisplay;

                fetch(`../fetch/wtm/wtm_check_overtime_application.php?employeeid=${employeeid}&shiftdate=${selected_date}`)
                    .then(response => response.json())
                    .then(result => {
                        if (parseInt(result.total) === 0) {
                            if (data.AllowedInOT == '1') {
                                if (data.overtime > 0) {
                                    overtimeHTML += `<button type="button" onclick="fileOvertime('${employeeid}', '${selected_date}')" class= "btn btn-sm btn-outline-success rounded-4 ms-2 mt-2 small">File Overtime</button>`;
                                }
                            } else {
                                overtimeHTML += `<div class= "alert alert-danger d-inline-block py-1 px-2 ms-2 mt-2 mb-0 small rounded-4">⚠ Need Overtime Permission</div > `;
                            }
                            if (data.AllowedInOT == '1') {
                                if (data.OpentimeOT == '1') {
                                    overtimeHTML += `<button type = "button" 
                                                    class= "btn btn-sm btn-outline-success rounded-4 ms-2 small" id = "btnFileOpentimeOvertime">File Opentime OT</button>`;
                                }
                            }
                        } else {
                            if (result.status == 'DECLINED' || result.status == 'CANCELLED') {
                                if (data.AllowedInOT == '1') {
                                    if (data.overtime > 0) {
                                        overtimeHTML += `<button type="button" onclick="fileOvertime('${employeeid}', '${selected_date}')" class= "btn btn-sm btn-outline-success rounded-4 ms-2 mb-2 small">File Overtime</button>`;
                                    }
                                } else {
                                    overtimeHTML += `<div class= "alert alert-danger d-inline-block py-1 px-2 ms-2 mt-2 mb-0 small rounded-4">⚠ Need Overtime Permission</div > `;
                                }
                                if (data.AllowedInOT == '1') {
                                    if (data.OpentimeOT == '1') {
                                        overtimeHTML += `<button type = "button" 
                                                    class= "btn btn-sm btn-outline-success rounded-4 ms-2 small" id = "btnFileOpentimeOvertime">File Opentime OT</button>`;
                                    }
                                }
                            } else {
                                overtimeHTML += `<button type = "button" class="btn btn-sm btn-outline-primary rounded-4 ms-2 mt-2 small" id = "btnChangeShift" onclick="ViewOvertime('${employeeid}', '${result.overtimeid}')">View Application<span class= "badge bg-warning ms-2"> ${result.status}</span></button>`;
                            }
                        }

                        overtimeHTML += `<button type = "button" class="btn btn-sm btn-outline-secondary rounded-4 ms-2 mt-2 small" data-bs-toggle="modal" data-bs-target="#OvertimeApplicationsModal">View All OT Application</button>`;

                        formModal.querySelector('#modal-overtime').innerHTML = overtimeHTML;
                    })
                    .catch(err => console.error(err));

                // Remarks
                formModal.querySelector('#modal-remarks').innerHTML = `<strong> ${data.remarks}</strong>`;
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
            }
        });
    });
}

initializeFormModal();

function initializeShiftModal(modalId = 'ChangeShiftModal') {
    const formModal = document.getElementById(modalId);
    if (!formModal) return;

    formModal.addEventListener('show.bs.modal', function (event) {

        const button = event.relatedTarget;
        if (!button) return;

        const area = document.getElementById('emp-area').value.trim();

        if (!employeeid || !selected_date) {
            console.error("Missing employeeId or date");
            return;
        }

        document.getElementById('hidden_date_selected').value = selected_date;

        // 1. Fetch Employee DTR Details
        $.ajax({
            url: '../fetch/wtm/wtm_dtr_details.php',
            type: 'POST',
            data: { employee_id: employeeid, date: selected_date },
            dataType: 'json',
            success: function (data) {
                if (data.error) {
                    console.error(data.error);
                    return;
                }

                // format helpers
                function formatDate(dateStr) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);
                    return isNaN(d.getTime()) ? '' : d.toLocaleDateString('en-US', {
                        month: 'short',
                        day: '2-digit',
                        year: 'numeric'
                    });
                }
                const formatTime = (timeStr) => (!timeStr || timeStr === '00:00:00') ? '' : timeStr.slice(0, 5);

                formModal.querySelector('#modal-date-value').textContent = formatDate(selected_date);
                let shiftText = `[${current_shiftcode}]`;

                if (current_shiftstart && current_shiftend && current_shiftstart.trim() !== "00:00:00" && current_shiftend.trim() !== "00:00:00") {
                    shiftText += ` ${formatTime(current_shiftstart)} - ${formatTime(current_shiftend)} `;
                }

                formModal.querySelector('#modal-in').textContent = shiftText;

                $.ajax({
                    url: '../fetch/wtm/wtm_changeschedule_shiftschedule.php',
                    type: 'POST',
                    data: { area: area },
                    dataType: 'json',
                    success: function (schedules) {
                        const dropdown = formModal.querySelector('#changeschedto_dropdown');

                        if (schedules.length === 0) {
                            dropdown.innerHTML = `<option value = "">No schedules found</option> `;
                            return;
                        }

                        dropdown.options.length = 1;

                        schedules.forEach(sc => {
                            // Skip current shift
                            if (sc.shiftcode === current_shiftcode) return;

                            const option = document.createElement('option');
                            option.value = sc.shiftcode;

                            const start = sc.shiftstart && sc.shiftstart.trim() !== "" ? sc.shiftstart.slice(0, 5) : "";
                            const end = sc.shiftend && sc.shiftend.trim() !== "" ? sc.shiftend.slice(0, 5) : "";
                            const desc = sc.shiftdescription && sc.shiftdescription.trim() !== "" ? ` (${sc.shiftdescription})` : "";

                            option.dataset.schedule = (start && end) ? `${start} - ${end} ` : "00:00 - 00:00";

                            let timePart = (start && end) ? `${start} - ${end} ` : "";
                            option.textContent = `[${sc.shiftcode}]${timePart}${desc} `;

                            dropdown.appendChild(option);
                        });


                    },
                    error: function (xhr, status, error) {
                        console.error("Schedule AJAX Error:", status, error);
                    }
                });
            },
            error: function (xhr, status, error) {
                console.error("DTR AJAX Error:", status, error);
            }
        });
    });
}

initializeShiftModal();


function initializeOTApplications(modalId = 'OvertimeApplicationsModal') {
    const formModal = document.getElementById(modalId);
    if (!formModal) return;

    formModal.addEventListener('show.bs.modal', function (event) {

        const button = event.relatedTarget;
        if (!button) return;

        if (!employeeid || !selected_date) {
            console.error("Missing employeeId or date");
            return;
        }

        document.getElementById('hidden_date_selected').value = selected_date;

        // 1. Fetch Employee DTR Details
        $.ajax({
            url: '../fetch/wtm/wtm_dtr_details.php',
            type: 'POST',
            data: { employee_id: employeeid, date: selected_date },
            dataType: 'json',
            success: function (data) {
                if (data.error) {
                    console.error(data.error);
                    return;
                }

                // format helpers
                function formatDate(dateStr) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);
                    return isNaN(d.getTime()) ? '' : d.toLocaleDateString('en-US', {
                        month: 'short',
                        day: '2-digit',
                        year: 'numeric'
                    });
                }
                const formatTime = (timeStr) => (!timeStr || timeStr === '00:00:00') ? '' : timeStr.slice(0, 5);

                // Fill modal with DTR data
                formModal.querySelector('#modal-date-value').textContent = formatDate(selected_date);
                let shiftText = `[${current_shiftcode}]`;

                if (current_shiftstart && current_shiftend && current_shiftstart.trim() !== "00:00:00" && current_shiftend.trim() !== "00:00:00") {
                    shiftText += ` ${formatTime(current_shiftstart)} - ${formatTime(current_shiftend)} `;
                }

                formModal.querySelector('#modal-in').textContent = shiftText;

                fetch(`../fetch/wtm/wtm_mydtr_overtime_list.php?employeeid=${encodeURIComponent(employeeid)}&shiftdate=${encodeURIComponent(selected_date)}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok: ' + response.status);
                        return response.json();
                    })
                    .then(data => {
                        const tbody = document.getElementById('employees_tbody');
                        tbody.innerHTML = '';

                        if (!Array.isArray(data) || data.length === 0) {
                            tbody.innerHTML = `<tr><td colspan="10" class="text-center text-muted">No employees found.</td></tr > `;
                            return;
                        }

                        data.forEach(row => {
                            const employeeid = row.employeeid;
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td class= "text-start text-wrap" style = "width:20%;"> ${row.overtimeid}</td>
                                <td class="text-start text-center text-wrap" style="width:20%;">
                                ${new Date(row.overtimedate).toLocaleDateString('en-US', {
                                month: 'short',
                                day: '2-digit',
                                year: 'numeric'
                            })}
                                </td>
                                <td class="text-start text-wrap text-center" style="width:5%;">${row.totalovertime}</td>
                                <td class="text-start text-wrap" style="width:15%;">${row.overtimetype}</td>
                                <td class="text-start text-wrap text-center" style="width:15%;">${row.status}</td>

                                <td class="text-center" style="width:10%;">
                                    <button class="btn btn-sm btn-dark mb-1" 
                                            data-employeeid="${employeeid}" 
                                            onclick="ViewOvertime('${employeeid}', '${row.overtimeid}')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>

                            `;
                            tbody.appendChild(tr);
                        });
                    })
                    .catch(err => console.error('Fetch error:', err));

            },
            error: function (xhr, status, error) {
                console.error("DTR AJAX Error:", status, error);
            }
        });
    });
}

initializeOTApplications();


//=====SUBMIT CHANGE SCHED REQUEST =====//

document.getElementById('SubmitChangeShift_Btn').addEventListener('click', function () {

    Swal.fire({
        title: '<span style="font-size: 1.3rem;">Do you want to submit the request for change shift schedule?</span>',
        showDenyButton: true,
        confirmButtonText: 'Yes',
        confirmButtonColor: '#28a745',
        denyButtonText: 'No',
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: {
            actions: 'my-actions',
            confirmButton: 'order-2',
            denyButton: 'order-3',
            title: 'swal-title-small',
        },
        didOpen: () => {
            const content = document.querySelector('.swal2-html-container');
            if (content) content.style.fontSize = '0.9rem';
        }
    }).then((result) => {
        if (result.isConfirmed) {

            if (this.disabled) return;
            this.disabled = true;
            this.textContent = 'Submitting...';

            const employeeId = employeeid;
            const shiftDateStr = document.getElementById('hidden_date_selected').value;
            const shiftDateObj = new Date(shiftDateStr);

            const currentShiftCode = current_shiftcode;
            const currentShiftSched = `${current_shiftstart.slice(0, 5)} - ${current_shiftend.slice(0, 5)} `;
            const newShiftCode = document.getElementById('changeschedto_dropdown').value;
            const newShiftSched = document.querySelector("#changeschedto_dropdown option:checked").dataset.schedule;
            const reason = document.getElementById('req_reason').value.trim();

            // Validate new shift selection
            if (!newShiftCode) {
                showAlert('Please select a new schedule.');
                document.getElementById('changeschedto_dropdown').focus();
                this.disabled = false;
                this.textContent = 'Submit request';
                return;
            }

            // Validate reason
            if (!reason) {
                showAlert('Please provide a reason for the shift change.');
                document.getElementById('req_reason').focus();
                this.disabled = false;
                this.textContent = 'Submit request';
                return;
            }

            // Generate request ID: employeeid-MMddyy-hhmmss
            const now = new Date();
            const pad = num => num.toString().padStart(2, '0');
            const requestId = `${employeeId} - ${pad(shiftDateObj.getMonth() + 1)
                }${pad(shiftDateObj.getDate())
                }${shiftDateObj.getFullYear().toString().slice(-2)} -${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())} `;

            const month = shiftDateObj.getMonth() + 1;
            const year = shiftDateObj.getFullYear();

            let params = `requestid = ${encodeURIComponent(requestId)} `;
            params += `& month=${encodeURIComponent(month)} `;
            params += `& year=${encodeURIComponent(year)} `;
            params += `& employeeid=${encodeURIComponent(employeeId)} `;
            params += `& shiftdate=${encodeURIComponent(shiftDateStr)} `;
            params += `& currentshiftcode=${encodeURIComponent(currentShiftCode)} `;
            params += `& currentshiftsched=${encodeURIComponent(currentShiftSched)} `;
            params += `& newshiftcode=${encodeURIComponent(newShiftCode)} `;
            params += `& newshiftsched=${encodeURIComponent(newShiftSched)} `;
            params += `& reason=${encodeURIComponent(reason)} `;
            params += `& status=PENDING`;

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "../inserts/wtm/wtm_changeshift_request_insert.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    Swal.fire({
                        title: '<span style="font-size: 1.5rem;">Request Submitted!</span>',
                        html: '<span style="font-size: 1.2rem;">Shift change request has been filed.</span>',
                        icon: "success",
                        confirmButtonText: "OK",
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(() => {
                        const modalEl = document.getElementById('ChangeShiftModal');
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        modal.hide();

                        const form = modalEl.querySelector('form');
                        if (form) form.reset();
                    });
                }
            };
            xhr.send(params);
        }
    });
});


function fileOvertime(employeeId, selected_date) {
    if (!employeeId || !selected_date) {
        console.error("Missing employeeId or date");
        return;
    }

    $.ajax({
        url: '../fetch/wtm/wtm_dtr_details.php', // adjust path if needed
        type: 'POST',
        data: { employee_id: employeeId, date: selected_date },
        dataType: 'json',
        success: function (data) {
            if (data.error) {
                console.error(data.error);
                return;
            }

            // Create a form dynamically
            var form = $('<form>', {
                method: 'POST',
                action: 'wtm_file_overtime.php'
            });

            // format helpers
            function formatDate(dateStr) {
                if (!dateStr) return '';
                const d = new Date(dateStr);
                return isNaN(d.getTime()) ? '' : d.toLocaleDateString('en-US', {
                    month: 'short',
                    day: '2-digit',
                    year: 'numeric'
                });
            }

            const formatTime = (timeStr) => (!timeStr || timeStr === '00:00:00') ? '' : timeStr.slice(0, 5);

            let shiftdisplay = `[${data.shiftcode}]`;

            if (data.shiftin && data.shiftout && data.shiftin.trim() !== "00:00:00" && data.shiftout.trim() !== "00:00:00") {
                shiftdisplay += ` ${formatTime(data.shiftin)
                    } - ${formatTime(data.shiftout)} `;
            }

            // Fields to send
            var fields = {
                employee_id: employeeId,
                date: selected_date,
                shiftDisplay: shiftdisplay || '',
                shiftcode: data.shiftcode || '',
                shiftstart: data.shiftin || '',
                shiftend: data.shiftout || '',
                datein: data.datein || '',
                timein: data.timein || '',
                dateout: data.dateout || '',
                timeout: data.timeout || '',
                overtime: data.overtime || ''
            };

            // Add hidden inputs
            for (var key in fields) {
                $('<input>').attr({
                    type: 'hidden',
                    name: key,
                    value: fields[key]
                }).appendTo(form);
            }

            // Append form to body and submit
            form.appendTo('body').submit();
        },
        error: function (xhr, status, error) {
            console.error("DTR AJAX Error:", status, error);
        }
    });
}


function ManualRequest(employeeId, selected_date) {
    if (!employeeId || !selected_date) {
        console.error("Missing employeeId or date");
        return;
    }

    $.ajax({
        url: '../fetch/wtm/wtm_dtr_details.php', // adjust path if needed
        type: 'POST',
        data: { employee_id: employeeId, date: selected_date },
        dataType: 'json',
        success: function (data) {
            if (data.error) {
                console.error(data.error);
                return;
            }

            // Create a form dynamically
            var form = $('<form>', {
                method: 'POST',
                action: 'wtm_manualattendance_request.php'
            });

            // format helpers
            function formatDate(dateStr) {
                if (!dateStr) return '';
                const d = new Date(dateStr);
                return isNaN(d.getTime()) ? '' : d.toLocaleDateString('en-US', {
                    month: 'short',
                    day: '2-digit',
                    year: 'numeric'
                });
            }

            const formatTime = (timeStr) => (!timeStr || timeStr === '00:00:00') ? '' : timeStr.slice(0, 5);

            let shiftdisplay = `[${data.shiftcode}]`;

            if (data.shiftin && data.shiftout && data.shiftin.trim() !== "00:00:00" && data.shiftout.trim() !== "00:00:00") {
                shiftdisplay += ` ${formatTime(data.shiftin)
                    } - ${formatTime(data.shiftout)} `;
            }

            // Fields to send
            var fields = {
                employee_id: employeeId,
                date: selected_date,
                shiftDisplay: shiftdisplay || '',
                shiftcode: data.shiftcode || '',
                shiftstart: data.shiftin || '',
                shiftend: data.shiftout || '',
                datein: data.datein || '',
                timein: data.timein || '',
                dateout: data.dateout || '',
                timeout: data.timeout || '',
                overtime: data.overtime || ''
            };

            // Add hidden inputs
            for (var key in fields) {
                $('<input>').attr({
                    type: 'hidden',
                    name: key,
                    value: fields[key]
                }).appendTo(form);
            }

            // Append form to body and submit
            form.appendTo('body').submit();
        },
        error: function (xhr, status, error) {
            console.error("DTR AJAX Error:", status, error);
        }
    });
}

function ViewOvertime(employeeId, overtimeId) {
    if (!employeeId || !overtimeId) return;

    $.ajax({
        url: '../fetch/wtm/wtm_overtime_employee_details.php',
        type: 'POST',
        data: { employee_id: employeeId, overtime_id: overtimeId },
        success: function (response) {
            console.log('AJAX Response:', response);
            window.location.href = 'wtm_viewonly_overtime.php';
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
            console.log('Response Text:', xhr.responseText);
        }
    });
}

function ChangeShift(employeeId, shiftdate) {
    if (!employeeId) return;

    $.ajax({
        url: '../fetch/wtm/wtm_changeshift_filing.php',
        type: 'POST',
        data: { employee_id: employeeId, shiftdate: shiftdate },
        success: function (response) {
            console.log('AJAX Response:', response);
            window.location.href = 'wtm_changeshift_filing.php';
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
            console.log('Response Text:', xhr.responseText);
        }
    });
}
