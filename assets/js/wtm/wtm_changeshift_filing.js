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

DTR_Tardiness();
DTR_Undertime();
DTR_Excess();
DTR_NightDiff();
DTR_Remarks();


