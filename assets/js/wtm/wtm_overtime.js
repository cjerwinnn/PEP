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

document.addEventListener('DOMContentLoaded', function () {
    const otInput = document.getElementById('ot-file');
    if (!otInput) return;

    otInput.value = parseFloat(otInput.value).toFixed(1);

    otInput.addEventListener('input', function () {
        let val = parseFloat(this.value);
        if (isNaN(val)) val = 0;

        val = Math.round(val * 2) / 2;
        this.value = val.toFixed(1);
    });
});


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
                    const emp_area = document.getElementById('area_data').value;

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
                    params += `&emp_area=${encodeURIComponent(emp_area)}`;
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


// ======= File Upload & Preview =======
let selectedFiles = [];
const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB

function handleFileSelect(event) {
    const fileInput = event.target;
    const files = Array.from(fileInput.files);
    let errorMessages = [];
    let successCount = 0;

    files.forEach(file => {
        if (file.size > MAX_FILE_SIZE) {
            errorMessages.push(`${file.name} (${formatFileSize(file.size)}) exceeds the 10MB size limit`);
            return;
        }

        if (!selectedFiles.some(existing => existing.name === file.name && existing.size === file.size)) {
            selectedFiles.push(file);
            successCount++;
        }
    });

    fileInput.value = '';

    if (errorMessages.length > 0) {
        errorMessages.forEach(msg => showAlert(msg, 'error'));
    }

    if (successCount > 0) {
        showSuccess(`Successfully added ${successCount} file${successCount > 1 ? 's' : ''}.`);
    }

    showFileList();
}

function showFileList() {
    const fileListElement = document.getElementById('fileList')?.getElementsByTagName('tbody')[0];
    const attachmentList = document.getElementById('attachment-list');

    if (!fileListElement || !attachmentList) return;

    fileListElement.innerHTML = '';

    if (selectedFiles.length === 0) {
        attachmentList.style.display = 'none';
        return;
    }

    attachmentList.style.display = 'block';

    selectedFiles.forEach((file, index) => {
        const row = document.createElement('tr');
        row.id = `file-${index}`;

        const fileSize = formatFileSize(file.size);
        const sizeClass = file.size <= MAX_FILE_SIZE ? 'text-success' : 'text-danger';

        row.innerHTML = `
            <td>${escapeHtml(file.name)}</td>
            <td class="${sizeClass}">${fileSize}</td>
            <td><button type="button" class="btn btn-danger btn-sm rounded-4" onclick="removeFile(${index})">Remove</button></td>
        `;
        fileListElement.appendChild(row);
    });

    const totalSize = selectedFiles.reduce((sum, file) => sum + file.size, 0);
    const totalRow = document.createElement('tr');
    totalRow.innerHTML = `
        <td><strong>Total Size</strong></td>
        <td colspan="2"><strong>${formatFileSize(totalSize)}</strong></td>
    `;
    fileListElement.appendChild(totalRow);
}

function removeFile(index) {
    try {
        selectedFiles.splice(index, 1);
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        const fileInput = document.getElementById('files');
        if (fileInput) {
            fileInput.files = dataTransfer.files;
        }
        showFileList();
    } catch (error) {
        console.error('Error removing file:', error);
        alert('There was an error removing the file. Please try again.');
    }
}

function formatFileSize(size) {
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    let i = 0;
    while (size >= 1024 && i < units.length - 1) {
        size /= 1024;
        i++;
    }
    return `${size.toFixed(2)} ${units[i]}`;
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function setupFileInput() {
    const fileInput = document.getElementById('files');
    if (fileInput) {
        fileInput.addEventListener('change', handleFileSelect);
        fileInput.dataset.maxFileSize = MAX_FILE_SIZE;
    }
}

const MAX_CHECKLIST_FILE_SIZE = 10 * 1024 * 1024; // 10MB

function setupChecklistFileInputs() {
    document.querySelectorAll('.file-input').forEach(input => {
        input.addEventListener('change', function () {
            const file = this.files[0];
            const container = this.closest('.d-flex');
            const fileNameSpan = container.querySelector('.file-name');
            const checkIcon = container.querySelector('.fa-check-circle');

            if (!file) {
                fileNameSpan.textContent = 'No file chosen';
                checkIcon.classList.add('d-none');
                return;
            }

            if (file.size > MAX_CHECKLIST_FILE_SIZE) {
                fileNameSpan.textContent = file.name + ' exceeds 10MB!';
                fileNameSpan.classList.add('text-danger');
                checkIcon.classList.add('d-none');
                showAlert(`${file.name} exceeds the 10MB size limit.`);
                this.value = ''; // Clear input
            } else {
                fileNameSpan.textContent = file.name;
                fileNameSpan.classList.remove('text-danger');
                checkIcon.classList.remove('d-none');
            }
        });
    });
}

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

//BIND CONTROLS

const fileInput = document.getElementById('files');
const browseBtn = document.getElementById('btnBrowseFiles');

if (fileInput && browseBtn) {
    browseBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', handleFileSelect);
}
