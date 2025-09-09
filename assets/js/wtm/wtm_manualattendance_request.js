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
    const dateInput = document.getElementById('schedule-date-in');
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

document.addEventListener('DOMContentLoaded', () => {
    const dateInput = document.getElementById('schedule-date-out');
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

document.addEventListener('DOMContentLoaded', () => {
    const timein_checkbox = document.getElementById('enable-time-in');
    const dateInput = document.getElementById('schedule-date-in');
    const timeInput = document.getElementById('schedule-time-in');

    if (timein_checkbox) { // only if checkbox exists
        timein_checkbox.addEventListener('change', () => {
            const enabled = timein_checkbox.checked;
            dateInput.disabled = !enabled;
            timeInput.disabled = !enabled;
        });
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const timeout_checkbox = document.getElementById('enable-time-out');
    const dateInput = document.getElementById('schedule-date-out');
    const timeInput = document.getElementById('schedule-time-out');

    if (timeout_checkbox) {
        // Checkbox exists
        timeout_checkbox.addEventListener('change', () => {
            const enabled = timeout_checkbox.checked;
            dateInput.disabled = !enabled;
            timeInput.disabled = !enabled;
        });
    }
});


//OVERTIME

document.getElementById('schedule-date-in').addEventListener('change', function () {
    DTR_Tardiness();
    DTR_Remarks();
});

document.getElementById('schedule-time-in').addEventListener('change', function () {
    DTR_Tardiness();
    DTR_Remarks();
});

//UNDERTIME

document.getElementById('schedule-date-out').addEventListener('change', function () {
    DTR_Undertime();
    DTR_Excess();
    DTR_NightDiff();
    DTR_Remarks();
});

document.getElementById('schedule-time-out').addEventListener('change', function () {
    DTR_Undertime();
    DTR_Excess();
    DTR_NightDiff();
    DTR_Remarks();
});

let tardiness_global = 0;
let undertime_global = 0;
let overtime_global = 0;
let nightdiff_global = 0;
let nd1012_global = 0;
let nd1206_global = 0;
let remarks_global = '';
let totalmanhours_global = 0;
let transactioncount_global = 0;


function DTR_Tardiness() {
    const shiftdate = document.getElementById('shiftdate_data');
    const shiftin = document.getElementById('shiftin_data');
    const shiftout = document.getElementById('shiftout_data');
    const datein = document.getElementById('schedule-date-in');
    const timein = document.getElementById('schedule-time-in');

    if (!datein.value || !timein.value) {
        document.getElementById('modal-tardiness').textContent = '-';
        tardiness_global = 0;
    } else {
        let tardiness = computeTardiness(shiftdate.value, shiftin.value, shiftout.value, datein.value, timein.value);
        const tardinessDisplay = (tardiness >= 0) ? `${tardiness} minute(s)` : '-';
        document.getElementById('modal-tardiness').textContent = tardinessDisplay;
        tardiness_global = tardiness;
    }
}

function DTR_Undertime() {
    const shiftdate = document.getElementById('shiftdate_data');
    const shiftin = document.getElementById('shiftin_data');
    const shiftout = document.getElementById('shiftout_data');
    const dateout = document.getElementById('schedule-date-out');
    const timeout = document.getElementById('schedule-time-out');

    if (!dateout.value || !timeout.value) {
        document.getElementById('modal-undertime').textContent = '-';
        undertime_global = 0;
    } else {
        let undertime = computeUndertime(shiftdate.value, shiftin.value, shiftout.value, dateout.value, timeout.value);
        const undertimeDisplay = (undertime >= 0) ? `${undertime} minute(s)` : '-';
        document.getElementById('modal-undertime').textContent = undertimeDisplay;
        undertime_global = undertime;
    }
}


function DTR_Excess() {
    const shiftdate = document.getElementById('shiftdate_data');
    const shiftin = document.getElementById('shiftin_data');
    const shiftout = document.getElementById('shiftout_data');
    const dateout = document.getElementById('schedule-date-out');
    const timeout = document.getElementById('schedule-time-out');

    if (!dateout.value || !timeout.value) {
        document.getElementById('modal-overtime').textContent = '-';
        overtime_global = 0;
    } else {
        let excesstime = computeOvertime(shiftdate.value, shiftin.value, shiftout.value, dateout.value, timeout.value);
        const ExcessDisplay = (excesstime >= 0) ? `${excesstime} Hour(s)` : '-';
        document.getElementById('modal-overtime').textContent = ExcessDisplay;
        overtime_global = excesstime;
    }
}

function DTR_NightDiff() {
    const datein = document.getElementById('schedule-date-in');
    const dateout = document.getElementById('schedule-date-out');
    const timein = document.getElementById('schedule-time-in');
    const timeout = document.getElementById('schedule-time-out');
    const ND_timeIn = new Date(`${datein.value}T${timein.value}`);
    const ND_timeOut = new Date(`${dateout.value}T${timeout.value}`);


    if (!timein.value || !timeout.value) {
        document.getElementById('modal-nightdiff').textContent = '-';
        nightdiff_global = 0;
        nd1012_global = 0;
        nd1206_global = 0;
    } else {
        let ndresult = calculateNightDiff(ND_timeIn, ND_timeOut);
        const totalNightDiff = roundToHalfMax8(ndresult.totalND);
        nd1012_global = roundToHalfMax8(ndresult.nd1);
        nd1206_global = roundToHalfMax8(ndresult.nd2);
        const NightDiffDisplay = (totalNightDiff >= 0) ? `${totalNightDiff} Hour(s)` : '-';
        document.getElementById('modal-nightdiff').textContent = NightDiffDisplay;
        nightdiff_global = totalNightDiff;
    }
}

async function DTR_Remarks() {
    const shiftdate = document.getElementById('shiftdate_data');
    const payrolltype = document.getElementById('employeepayrolltype_data').value;
    const shiftcode = document.getElementById('shiftcode_data').value;
    const shiftin = document.getElementById('shiftin_data');
    const shiftout = document.getElementById('shiftout_data');
    const timein = document.getElementById('schedule-time-in');
    const timeout = document.getElementById('schedule-time-out');

    const other_dtrdate = await GetDTRRemarks(shiftdate.value, shiftcode, shiftin.value, shiftout.value, timein.value, timeout.value, 0, payrolltype);

    remarks_global = other_dtrdate.remdetails;
    totalmanhours_global = other_dtrdate.TMH_Value;
    transactioncount_global = other_dtrdate.tcount;

    document.getElementById('modal-remarks').textContent = other_dtrdate.remdetails;
    document.getElementById('modal-manhrs').textContent = (other_dtrdate.TMH_Value > 0) ? `${other_dtrdate.TMH_Value} Hour(s)` : '-';
    document.getElementById('modal-trancount').textContent = (other_dtrdate.tcount > 0) ? `${other_dtrdate.tcount}` : '-';

}
//=====SUBMIT CHANGE SCHED REQUEST =====//

document.getElementById('SubmitChangeShift_Btn').addEventListener('click', function () {
    const button = this; // save reference to the button
    const current_date = document.getElementById('shiftdate_data').value;

    $.ajax({
        url: '../fetch/wtm/wtm_dtr_details.php',
        type: 'POST',
        data: { employee_id: employeeid, date: current_date },
        dataType: 'json',
        success: function (data) {
            if (data.error) {
                console.error(data.error);
                return;
            }

            if (button.disabled) return;
            button.disabled = true;
            button.textContent = 'Submitting...';

            const employeeId = data.employeeid;
            const employeename = document.getElementById('employeename_data').value;
            const department = document.getElementById('department_data').value;
            const area = document.getElementById('area_data').value;
            const position = document.getElementById('position_data').value;
            const shiftDateStr = data.date;
            const shiftDateObj = new Date(shiftDateStr);
            const days = ["SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT"];
            const dayOfWeek = days[shiftDateObj.getDay()];

            const shiftcode = data.shiftcode;
            const shiftin = data.shiftin;
            const shiftout = data.shiftout;

            const dateInInput = document.getElementById('schedule-date-in').value;
            const dateOutInput = document.getElementById('schedule-date-out').value;
            const timeInInput = document.getElementById('schedule-time-in').value;
            const timeOutInput = document.getElementById('schedule-time-out').value;

            const reason = document.getElementById('req_reason').value.trim();

            // validation checks
            if (!dateInInput) {
                showAlert('Date In is required for Manual In/Out request.');
                document.getElementById('schedule-date-in').focus();
                button.disabled = false;
                button.textContent = 'Submit request';
                return;
            }

            if (!timeInInput) {
                showAlert('Time In is required for Manual In/Out request.');
                document.getElementById('schedule-time-in').focus();
                button.disabled = false;
                button.textContent = 'Submit request';
                return;
            }

            if (!dateOutInput) {
                showAlert('Date Out is required for Manual In/Out request.');
                document.getElementById('schedule-date-out').focus();
                button.disabled = false;
                button.textContent = 'Submit request';
                return;
            }

            if (!timeOutInput) {
                showAlert('Time Out is required for Manual In/Out request.');
                document.getElementById('schedule-time-out').focus();
                button.disabled = false;
                button.textContent = 'Submit request';
                return;
            }

            if (!reason) {
                showAlert('Please provide a justification for the overtime application.');
                document.getElementById('req_reason').focus();
                button.disabled = false;
                button.textContent = 'Submit request';
                return;
            }

            Swal.fire({
                title: '<span style="font-size: 1.3rem;">Do you want to submit the request for Manual In/Out?</span>',
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

                    // Generate request ID: employeeid-MMddyy-hhmmss
                    const now = new Date();
                    const pad = num => num.toString().padStart(2, '0');
                    const RequestID = `${employeeId}-${pad(shiftDateObj.getMonth() + 1)}${pad(shiftDateObj.getDate())}${shiftDateObj.getFullYear().toString().slice(-2)}-${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}`;

                    const CurrentUser = document.getElementById('current_user').value;

                    let params = `requestid=${encodeURIComponent(RequestID)}`;
                    params += `&employeeid=${encodeURIComponent(employeeId)}`;
                    params += `&employeename=${encodeURIComponent(employeename)}`;
                    params += `&department=${encodeURIComponent(department)}`;
                    params += `&area=${encodeURIComponent(area)}`;
                    params += `&position=${encodeURIComponent(position)}`;
                    params += `&dtrdate=${encodeURIComponent(shiftDateStr)}`;
                    params += `&dayOfWeek=${encodeURIComponent(dayOfWeek)}`;
                    params += `&shiftcode=${encodeURIComponent(shiftcode)}`;
                    params += `&shiftin=${encodeURIComponent(shiftin)}`;
                    params += `&shiftout=${encodeURIComponent(shiftout)}`;
                    params += `&datein=${encodeURIComponent(dateInInput)}`;
                    params += `&timein=${encodeURIComponent(timeInInput)}`;
                    params += `&dateout=${encodeURIComponent(dateOutInput)}`;
                    params += `&timeout=${encodeURIComponent(timeOutInput)}`;
                    params += `&tardiness_global=${encodeURIComponent(tardiness_global)}`;
                    params += `&undertime_global=${encodeURIComponent(undertime_global)}`;
                    params += `&overtime_global=${encodeURIComponent(overtime_global)}`;
                    params += `&nightdiff_global=${encodeURIComponent(nightdiff_global)}`;
                    params += `&nd_1012_global=${encodeURIComponent(nd1012_global)}`;
                    params += `&nd_1206_global=${encodeURIComponent(nd1206_global)}`;
                    params += `&remarks_global=${encodeURIComponent(remarks_global)}`;
                    params += `&totalmanhours_global=${encodeURIComponent(totalmanhours_global)}`;
                    params += `&transactioncount_global=${encodeURIComponent(transactioncount_global)}`;
                    params += `&reason=${encodeURIComponent(reason)}`;
                    params += `&currentuser=${encodeURIComponent(CurrentUser)}`;
                    params += `&status=PENDING`;

                    let xhr = new XMLHttpRequest();
                    xhr.open("POST", "../inserts/wtm/wtm_manualinout_request_insert.php", true);
                    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

                    xhr.onreadystatechange = function () {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            const page = 'wtm_mydtr.php';
                            window.location.href = page;
                        }
                    };
                    xhr.send(params);
                }
            });
        },
        error: function (xhr, status, error) {
            console.error("DTR AJAX Error:", status, error);
        }
    });
});


DTR_Tardiness();
DTR_Undertime();
DTR_Excess();
DTR_NightDiff();
DTR_Remarks();


