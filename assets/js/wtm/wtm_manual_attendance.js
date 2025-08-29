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

//=====SUBMIT CHANGE SCHED REQUEST =====//

document.getElementById('SubmitChangeShift_Btn').addEventListener('click', function () {
    Swal.fire({
        title: '<span style="font-size: 1.3rem;">Do you want to submit the application for overtime?</span>',
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

                    if (this.disabled) return;
                    this.disabled = true;
                    this.textContent = 'Submitting...';

                    const employeeId = data.employeeid;
                    const shiftDateStr = data.date;
                    const shiftDateObj = new Date(shiftDateStr);
                    const ExcessHRS = data.overtime;
                    const FileOT = document.getElementById('ot-file');
                    const OT_Start = data.shiftout.slice(0, 5);
                    const OT_End = data.timeout.slice(0, 5);
                    const OT_type = document.getElementById('overtimetype_dropdown').value;
                    const reason = document.getElementById('req_reason').value.trim();

                    if (FileOT.value > ExcessHRS) {
                        showAlert('Overtime hour(s) to be filed should not be more than excess hour(s).');
                        FileOT.value = ExcessHRS;
                        FileOT.max = ExcessHRS;
                        FileOT.min = "1.0";
                        FileOT.step = "0.5";
                        FileOT.focus();
                        this.disabled = false;
                        this.textContent = 'Submit application';
                        return;
                    }

                    if (!OT_type) {
                        showAlert('Please select a overtime type.');
                        document.getElementById('overtimetype_dropdown').focus();
                        this.disabled = false;
                        this.textContent = 'Submit application';
                        return;
                    }

                    if (!reason) {
                        showAlert('Please provide a justification for the overtime application.');
                        document.getElementById('req_reason').focus();
                        this.disabled = false;
                        this.textContent = 'Submit application';
                        return;
                    }

                    // Generate request ID: employeeid-MMddyy-hhmmss
                    const now = new Date();
                    const pad = num => num.toString().padStart(2, '0');
                    const OvertimeID = `${employeeId}-${pad(shiftDateObj.getMonth() + 1)}${pad(shiftDateObj.getDate())}${shiftDateObj.getFullYear().toString().slice(-2)}-${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}`;

                    const month = shiftDateObj.getMonth() + 1;
                    const year = shiftDateObj.getFullYear();

                    const ApplicationType = 'EXCESS';
                    const NextDay = 0;

                    let params = `overtimeid=${encodeURIComponent(OvertimeID)}`;
                    params += `&month=${encodeURIComponent(month)}`;
                    params += `&year=${encodeURIComponent(year)}`;
                    params += `&applicationtype=${encodeURIComponent(ApplicationType)}`;
                    params += `&employeeid=${encodeURIComponent(employeeId)}`;
                    params += `&overtimedate=${encodeURIComponent(shiftDateStr)}`;
                    params += `&overtimestart=${encodeURIComponent(OT_Start)}`;
                    params += `&overtimeend=${encodeURIComponent(OT_End)}`;
                    params += `&nextday=${encodeURIComponent(NextDay)}`;
                    params += `&totalovertime=${encodeURIComponent(FileOT.value)}`;
                    params += `&overtimetype=${encodeURIComponent(OT_type)}`;
                    params += `&reason=${encodeURIComponent(reason)}`;
                    params += `&status=PENDING`;

                    let xhr = new XMLHttpRequest();
                    xhr.open("POST", "../inserts/wtm/wtm_overtime_application_insert.php", true);
                    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

                    xhr.onreadystatechange = function () {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            uploadSelectedFiles(OvertimeID, employeeid)
                        }
                    };
                    xhr.send(params);

                },
                error: function (xhr, status, error) {
                    console.error("DTR AJAX Error:", status, error);
                }
            });
        }
    });
});

//Upload Attachments

function uploadSelectedFiles(overtimeid, employeeid) {
    const formData = new FormData();

    // Append the files
    selectedFiles.forEach((file, index) => {
        formData.append('files[]', file);
    });

    // Append the br_number parameter
    formData.append('overtime_id', overtimeid);
    formData.append('user_id', employeeid);

    fetch('../uploads/wtm_overtime_attachment_upload.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(result => {
            console.log('Success:', result);
            selectedFiles = [];
            showFileList();
            Swal.fire({
                title: '<span style="font-size: 1.5rem;">Application Submitted!</span>',
                html: '<span style="font-size: 1.2rem;">Overtime application has been submitted.</span>',
                icon: "success",
                confirmButtonText: "OK",
                confirmButtonColor: "#0d6efd",
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                if (result.isConfirmed) {
                    const page = 'wtm_mydtr.php';
                    window.location.href = page;
                }
            });
        })
        .catch(error => {
            console.error('Error uploading files:', error);
            alert('File upload failed.');
        });
}

//OVERTIME

document.getElementById('schedule-date-in').addEventListener('change', function () {
    DTR_Tardiness();
});

document.getElementById('schedule-time-in').addEventListener('change', function () {
    DTR_Tardiness();
});

//UNDERTIME

document.getElementById('schedule-date-out').addEventListener('change', function () {
    DTR_Undertime();
    DTR_Excess();
    DTR_NightDiff();
});

document.getElementById('schedule-time-out').addEventListener('change', function () {
    DTR_Undertime();
    DTR_Excess();
    DTR_NightDiff();
});


function DTR_Tardiness() {
    const shiftdate = document.getElementById('shiftdate_data');
    const shiftin = document.getElementById('shiftin_data');
    const shiftout = document.getElementById('shiftout_data');
    const datein = document.getElementById('schedule-date-in');
    const timein = document.getElementById('schedule-time-in');

    if (!datein.value || !timein.value) {
        document.getElementById('modal-tardiness').textContent = '-';
    } else {
        let tardiness = computeTardiness(shiftdate.value, shiftin.value, shiftout.value, datein.value, timein.value);
        const tardinessDisplay = (tardiness >= 0) ? `${tardiness} minute(s)` : '-';
        document.getElementById('modal-tardiness').textContent = tardinessDisplay;
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
    } else {
        let undertime = computeUndertime(shiftdate.value, shiftin.value, shiftout.value, dateout.value, timeout.value);
        const undertimeDisplay = (undertime >= 0) ? `${undertime} minute(s)` : '-';
        document.getElementById('modal-undertime').textContent = undertimeDisplay;
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
    } else {
        let excesstime = computeOvertime(shiftdate.value, shiftin.value, shiftout.value, dateout.value, timeout.value);
        const ExcessDisplay = (excesstime >= 0) ? `${excesstime} Hour(s)` : '-';
        document.getElementById('modal-overtime').textContent = ExcessDisplay;
    }
}

function DTR_NightDiff() {
    const datein = document.getElementById('schedule-date-in');
    const dateout = document.getElementById('schedule-date-out');
    const timein = document.getElementById('schedule-time-in');
    const timeout = document.getElementById('schedule-time-out');
    const ND_timeIn = new Date(`${datein.value}T${timein.value}`);
    const ND_timeOut = new Date(`${dateout.value}T${timeout.value}`);

    // const nd1Hours = roundToHalfMax8(ndresult.nd1);
    // const nd2Hours = roundToHalfMax8(ndresult.nd2);

    if (!timein.value || !timeout.value) {
        document.getElementById('modal-nightdiff').textContent = '-';
    } else {
        let ndresult = calculateNightDiff(ND_timeIn, ND_timeOut);
        const totalNightDiff = roundToHalfMax8(ndresult.totalND);
        const NightDiffDisplay = (totalNightDiff >= 0) ? `${totalNightDiff} Hour(s)` : '-';
        document.getElementById('modal-nightdiff').textContent = totalNightDiff;
    }
}





