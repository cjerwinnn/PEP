function generateCOEId() {
  const now = new Date();

  const pad = (num) => num.toString().padStart(2, '0');

  const MM = pad(now.getMonth() + 1); // Months are zero-based
  const dd = pad(now.getDate());
  const yy = now.getFullYear().toString().slice(-2);
  const HH = pad(now.getHours());
  const mm = pad(now.getMinutes());
  const ss = pad(now.getSeconds());

  return `COE_${MM}${dd}${yy}_${HH}${mm}${ss}`;
}

// ======= Alert Functions =======

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
    }, 2000);
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

// ======= Textarea Auto-Resize =======
function adjustTextareaHeight(textarea) {
  textarea.style.height = 'auto';
  textarea.style.height = textarea.scrollHeight + 'px';
}

function bindTextareaAutoResize(textareaId) {
  const textarea = document.getElementById(textareaId);
  if (textarea) {
    textarea.addEventListener("input", function () {
      adjustTextareaHeight(this);
    });
    adjustTextareaHeight(textarea); // Initial resize
  }
}

// ======= DOM Ready Initializations =======
document.addEventListener('DOMContentLoaded', () => {
  setupFileInput(); // Initialize file input after DOM is ready
});



// ======= Simple Popup Bind =======
function bindMyButtonPopup(buttonId) {
  const btn = document.getElementById(buttonId);
  if (btn) {
    btn.addEventListener("click", function () {
      showAlert("Hello! This is your popup message.");
    });
  }
}

//======= Summary ======= //
function formatDate(dateString) {
  const options = { year: 'numeric', month: 'short', day: '2-digit' };
  const date = new Date(dateString);
  return isNaN(date) ? '' : date.toLocaleDateString('en-US', options);
}

function infoRow(label, value) {
  return `
    <div class="d-flex">
      <div class="me-2" style="min-width: 160px;"><strong>${label}:</strong></div>
      <div>${value}</div>
    </div>
  `;
}

function ShowSummary(buttonId) {
  const btn = document.getElementById(buttonId);
  if (btn) {
    btn.addEventListener("click", function () {
      const empNameElem = document.getElementById('emp_name');
      const CoeTypeElem = document.getElementById('req_coe_type');
      const empIdElem = document.getElementById('emp_id');
      const deptElem = document.getElementById('emp_dept');
      const areaElem = document.getElementById('emp_area');
      const positionElem = document.getElementById('emp_position');
      const reasonElem = document.getElementById('req_reason');
      const dateNeededElem = document.getElementById('date_needed');
      const receivingFormatElem = document.getElementById('receivingFormat');

      const empName = empNameElem.value.trim();
      const CoeType = CoeTypeElem.value.trim();
      const empId = empIdElem.value.trim();
      const dept = deptElem.value.trim();
      const area = areaElem.value.trim();
      const position = positionElem.value.trim();
      const receivingFormat = receivingFormatElem.selectedOptions[0]?.text || '';
      const dateNeeded = dateNeededElem.value.trim();
      const reason = reasonElem.value.trim();

      // Validation with alert and focus
      if (!empName) {
        empNameElem.focus();
        return showAlert("Please enter Employee Name.");
      }
      if (!empId) {
        empIdElem.focus();
        return showAlert("Please enter Employee ID.");
      }
      if (!dept) {
        deptElem.focus();
        return showAlert("Please enter Department.");
      }
      if (!area) {
        areaElem.focus();
        return showAlert("Please enter Area/Section.");
      }
      if (!position) {
        positionElem.focus();
        return showAlert("Please enter Position.");
      }
      if (!reason) {
        reasonElem.focus();
        return showAlert("Please enter Reason.");
      }
      if (!dateNeeded) {
        dateNeededElem.focus();
        return showAlert("Please select Date Needed.");
      }
      if (receivingFormat == 'Select an option') {
        receivingFormatElem.focus();
        return showAlert("Please select Receiving Format.");
      }

      if ($('#req_coe_type').val() == 'TRAVEL') {
        const dateFromElem = document.getElementById('dateFrom');
        const dateToElem = document.getElementById('dateTo');
        const dateReturnElem = document.getElementById('date_return');
        const travelTypeElem = document.getElementById('receiving_Format');
        const locationElem = document.getElementById('travel_location');

        const dateFrom = dateFromElem.value.trim();
        const dateTo = dateToElem.value.trim();
        const dateReturn = dateReturnElem.value.trim();
        const travelType = travelTypeElem.selectedOptions[0]?.text || '';
        const location = locationElem.value.trim();

        if (!dateFrom) {
          dateFromElem.focus();
          return showAlert("Please select Travel Date From.");
        }
        if (!dateTo) {
          dateToElem.focus();
          return showAlert("Please select Travel Date To.");
        }
        if (!dateReturn) {
          dateReturnElem.focus();
          return showAlert("Please select Return to Work Date.");
        }
        if (travelType == 'Select an option') {
          travelTypeElem.focus();
          return showAlert("Please select Travel Type.");
        }
        if (!location) {
          locationElem.focus();
          return showAlert("Please enter Travel Location.");
        }
      }

      if ($('#req_coe_type').val() == 'BENEFIT CLAIM' || $('#req_coe_type').val() === 'BENEFIT CLAIM WITH COMPENSATION') {
        const ClaimTypeElem = document.getElementById('Claim_Type');
        const ClaimType = ClaimTypeElem.selectedOptions[0]?.text || '';

        if (ClaimType == 'Select an option') {
          ClaimTypeElem.focus();
          return showAlert("Please select Claim Type.");
        }
      }

      let travelInfoHtml = '';

      if ($('#req_coe_type').val() === 'TRAVEL') {
        const dateFromElem = document.getElementById('dateFrom');
        const dateToElem = document.getElementById('dateTo');
        const dateReturnElem = document.getElementById('date_return');
        const travelTypeElem = document.getElementById('receiving_Format');
        const locationElem = document.getElementById('travel_location');

        const dateFrom = dateFromElem.value.trim();
        const dateTo = dateToElem.value.trim();
        const dateReturn = dateReturnElem.value.trim();
        const travelType = travelTypeElem.selectedOptions[0]?.text || '';
        const location = locationElem.value.trim();

        travelInfoHtml = `
    <div class="p-3 bg-light rounded-3 shadow-sm">
      <h6 class="mb-1 text-primary">Travel Information</h6>
      ${infoRow("Travel Dates", `${formatDate(dateFrom)} - ${formatDate(dateTo)}`)}
      ${infoRow("Return to Work Date", formatDate(dateReturn))}
      ${infoRow("Travel Type", travelType)}
      ${infoRow("Location", location)}
    </div>
  `;
      }

      if ($('#req_coe_type').val() === 'BENEFIT CLAIM' || $('#req_coe_type').val() === 'BENEFIT CLAIM WITH COMPENSATION') {
        const Claim_TypeElemElem = document.getElementById('Claim_Type');

        const Claim_Type = Claim_TypeElemElem.selectedOptions[0]?.text || '';

        travelInfoHtml = `
    <div class="p-3 bg-light rounded-3 shadow-sm">
      <h6 class="mb-1 text-primary">Claim Information</h6>
      ${infoRow("Claim Type", Claim_Type)}
      
    </div>
  `;
      }

      const summaryHtml = `
  <div class="d-flex flex-column gap-3">
    <div class="p-3 bg-light rounded-3 shadow-sm">
      <h6 class="mb-1 text-primary">Employee Details</h6>
      ${infoRow("Employee ID", empId)}
      ${infoRow("Name", empName)}
      ${infoRow("Department", dept)}
      ${infoRow("Area/Section", area)}
      ${infoRow("Position", position)}
    </div>

    <div class="p-3 bg-light rounded-3 shadow-sm">
      <h6 class="mb-1 text-primary">Request Details</h6>
            ${infoRow("COE Type", CoeType)}
      <br>
      ${infoRow("Reason", reason.replace(/\n/g, '<br>'))}
      ${infoRow("Date Needed", formatDate(dateNeeded))}
      ${infoRow("Receiving Format", receivingFormat)}
    </div>

    ${travelInfoHtml}
  </div>
`;

      // Open the SubmitModal
      const modal = new bootstrap.Modal(document.getElementById('SubmitModal'));
      modal.show();

      document.getElementById('summary-content').innerHTML = summaryHtml;
    });
  }
}

// ======= Insert Database =======

function ConfirmRequest(buttonId) {
  const btn = document.getElementById(buttonId);
  if (btn) {
    btn.addEventListener("click", function () {
      const formData = new FormData();

      // Header Data
      formData.append('request_id', $('#req_id').val());
      formData.append('employee_id', $('#emp_id').val());
      formData.append('req_coe_type', $('#req_coe_type').val());
      formData.append('request_reason', $('#req_reason').val());
      formData.append('date_needed', $('#date_needed').val());
      formData.append('request_format', $('#receivingFormat').val());
      formData.append('requested_date', new Date().toISOString().split('T')[0]);
      formData.append('requested_time', new Date().toLocaleTimeString('en-GB'));
      formData.append('requested_by', $('#emp_id').val());
      formData.append('request_status', 'PENDING');

      const req_Id = $('#req_id').val();
      const user_Id = $('#emp_id').val();

      if ($('#req_coe_type').val() == 'BENEFIT CLAIM' || $('#req_coe_type').val() === 'BENEFIT CLAIM WITH COMPENSATION') {
        // Travel Data
        formData.append('claim_type', $('#Claim_Type').val());
      }

      if ($('#req_coe_type').val() == 'TRAVEL') {
        // Travel Data
        formData.append('travel_datefrom', $('#dateFrom').val());
        formData.append('travel_dateto', $('#dateTo').val());
        formData.append('date_return', $('#date_return').val());
        formData.append('travel_type', $('#receiving_Format').val());
        formData.append('travel_location', $('#travel_location').val());
      }

      $.ajax({
        url: 'inserts/request_coe_insert.php',
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
          console.log('Server Response:', response);
          uploadSelectedFiles(req_Id, user_Id);
        },
        error: function (xhr, status, error) {
          console.error('Error:', error);
          showAlert('Error submitting request.');
          $('#yes-btn').prop('disabled', false);
          $('#no-btn').prop('disabled', false);
        }
      });
    });
  }
}

//Upload Attachments

function uploadSelectedFiles(req_id, userId) {
  const formData = new FormData();

  // Append the files
  selectedFiles.forEach((file, index) => {
    formData.append('files[]', file);
  });

  // Append the br_number parameter
  formData.append('request_id', req_id);
  formData.append('user_id', userId);

  fetch('uploads/request_coe_upload.php', {
    method: 'POST',
    body: formData
  })
    .then(response => response.text())
    .then(result => {
      console.log('Success:', result);
      selectedFiles = [];
      showFileList();
      Swal.fire({
        title: "Submitted!",
        text: "Your request was submitted.",
        icon: "success",
        confirmButtonText: "OK",
        confirmButtonColor: "#0d6efd",
        allowOutsideClick: false,
        allowEscapeKey: false
      }).then((result) => {
        if (result.isConfirmed) {
          const page = 'request_coe_list.php';
          fetch(page)
            .then(response => response.text())
            .then(data => {
              document.getElementById('main-content').innerHTML = data;
            })
            .catch(error => {
              document.getElementById('main-content').innerHTML = '<p>Error loading content.</p>';
              console.error('Error:', error);
            });
        }
      });
    })
    .catch(error => {
      console.error('Error uploading files:', error);
      alert('File upload failed.');
    });
}

// Approved

function ApprovedCOERequest(buttonId) {
  const btn = document.getElementById(buttonId);
  if (btn) {
    btn.addEventListener("click", function () {
      const formData = new FormData();

      // Header Data
      formData.append('request_id', $('#req_id').val());
      formData.append('employee_id', $('#emp_id').val());
      formData.append('req_coe_type', $('#req_coe_type').val());
      formData.append('request_reason', $('#req_reason').val());
      formData.append('date_needed', $('#date_needed').val());
      formData.append('request_format', $('#receivingFormat').val());
      formData.append('requested_date', new Date().toISOString().split('T')[0]);
      formData.append('requested_time', new Date().toLocaleTimeString('en-GB'));
      formData.append('requested_by', $('#emp_id').val());
      formData.append('tagged_by', $('#current_user').val());
      formData.append('request_status', 'APPROVED');
      //APPROVAL REMARKS
      formData.append('approval_remarks', $('#approval_remarks').val());
      const req_Id = $('#req_id').val();
      const user_Id = $('#emp_id').val();

      $.ajax({
        url: 'updates/request_coe_approved_update.php',
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
              const page = 'request_coe_list.php';
              fetch(page)
                .then(response => response.text())
                .then(data => {
                  document.getElementById('main-content').innerHTML = data;
                })
                .catch(error => {
                  document.getElementById('main-content').innerHTML = '<p>Error loading content.</p>';
                  console.error('Error:', error);
                });
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
  }
}


// Declined

function DeclinedCOERequest(buttonId) {
  const btn = document.getElementById(buttonId);
  if (btn) {
    btn.addEventListener("click", function () {
      const declineReasonElem = document.getElementById('decline_reason');
      const declineReason = declineReasonElem ? declineReasonElem.value.trim() : '';

      // Check if decline reason is empty
      if (!declineReason) {
        if (declineReasonElem) declineReasonElem.focus();
        return showAlert("Please enter the reason for declining the request.");
      }

      $('#DeclineModal').modal('hide');

      const formData = new FormData();

      // Header Data
      formData.append('request_id', $('#req_id').val());
      formData.append('employee_id', $('#emp_id').val());
      formData.append('req_coe_type', $('#req_coe_type').val());
      formData.append('request_reason', $('#req_reason').val());
      formData.append('date_needed', $('#date_needed').val());
      formData.append('request_format', $('#receivingFormat').val());
      formData.append('requested_date', new Date().toISOString().split('T')[0]);
      formData.append('requested_time', new Date().toLocaleTimeString('en-GB'));
      formData.append('requested_by', $('#emp_id').val());
      formData.append('tagged_by', $('#current_user').val());
      formData.append('request_status', 'DECLINED');

      formData.append('decline_reason', $('#decline_reason').val());
      const req_Id = $('#req_id').val();
      const user_Id = $('#emp_id').val();

      $.ajax({
        url: 'updates/request_coe_declined_update.php',
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
            confirmButtonColor: "#dc3545",
            allowOutsideClick: false,
            allowEscapeKey: false
          }).then((result) => {
            if (result.isConfirmed) {
              const page = 'request_coe_list.php';
              fetch(page)
                .then(response => response.text())
                .then(data => {
                  document.getElementById('main-content').innerHTML = data;
                })
                .catch(error => {
                  document.getElementById('main-content').innerHTML = '<p>Error loading content.</p>';
                  console.error('Error:', error);
                });
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
  }
}


// On Process

function COE_OnProcess(buttonId) {
  const btn = document.getElementById(buttonId);
  if (btn) {
    btn.addEventListener("click", function () {
      $('#OnProcessModal').modal('hide');

      const formData = new FormData();

      // Header Data
      formData.append('request_id', $('#req_id').val());
      formData.append('employee_id', $('#emp_id').val());
      formData.append('req_coe_type', $('#req_coe_type').val());
      formData.append('request_reason', $('#req_reason').val());
      formData.append('date_needed', $('#date_needed').val());
      formData.append('request_format', $('#receivingFormat').val());
      formData.append('requested_date', new Date().toISOString().split('T')[0]);
      formData.append('requested_time', new Date().toLocaleTimeString('en-GB'));
      formData.append('requested_by', $('#requested_by').val());
      formData.append('tagged_by', $('#current_user').val());
      formData.append('request_status', 'ON PROCESS');

      formData.append('onprocess_remarks', $('#onprocess_remarks').val());
      const req_Id = $('#req_id').val();
      const user_Id = $('#requested_by').val();

      const coe_type = $('#req_coe_type').val();

      if (coe_type === 'BENEFIT CLAIM' || coe_type === 'BENEFIT CLAIM WITH COMPENSATION') {
        formData.append('compensation_details', $('#compensation_details').val());
      }

      $.ajax({
        url: 'updates/request_coe_onprocess_update.php',
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
            title: "Tagged!",
            text: "Request Status: ON PROCESS",
            icon: "success",
            confirmButtonText: "OK",
            confirmButtonColor: "#0d6efd",
            allowOutsideClick: false,
            allowEscapeKey: false
          }).then((result) => {
            if (result.isConfirmed) {
              const page = 'request_coe_hr_list.php';
              fetch(page)
                .then(response => response.text())
                .then(data => {
                  document.getElementById('main-content').innerHTML = data;
                })
                .catch(error => {
                  document.getElementById('main-content').innerHTML = '<p>Error loading content.</p>';
                  console.error('Error:', error);
                });
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
  }
}


// For Signing

function COE_ForSigning(buttonId) {
  const btn = document.getElementById(buttonId);
  if (btn) {
    btn.addEventListener("click", function () {
      $('#ForSigningModal').modal('hide');

      const formData = new FormData();

      // Header Data
      formData.append('request_id', $('#req_id').val());
      formData.append('employee_id', $('#emp_id').val());
      formData.append('req_coe_type', $('#req_coe_type').val());
      formData.append('request_reason', $('#req_reason').val());
      formData.append('date_needed', $('#date_needed').val());
      formData.append('request_format', $('#receivingFormat').val());
      formData.append('requested_date', new Date().toISOString().split('T')[0]);
      formData.append('requested_time', new Date().toLocaleTimeString('en-GB'));
      formData.append('requested_by', $('#requested_by').val());
      formData.append('tagged_by', $('#current_user').val());
      formData.append('request_status', 'FOR SIGNING');

      formData.append('remarks', $('#forsigning_remarks').val());
      const req_Id = $('#req_id').val();
      const user_Id = $('#requested_by').val();

      $.ajax({
        url: 'updates/request_coe_forsigning_update.php',
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
            title: "Tagged!",
            text: "Request Status: FOR SIGNING",
            icon: "success",
            confirmButtonText: "OK",
            confirmButtonColor: "#ffc107",
            allowOutsideClick: false,
            allowEscapeKey: false
          }).then((result) => {
            if (result.isConfirmed) {
              const page = 'request_coe_hr_list.php';
              fetch(page)
                .then(response => response.text())
                .then(data => {
                  document.getElementById('main-content').innerHTML = data;
                })
                .catch(error => {
                  document.getElementById('main-content').innerHTML = '<p>Error loading content.</p>';
                  console.error('Error:', error);
                });
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
  }
}


// For Releasing

function COE_ForReleasing(buttonId) {
  const btn = document.getElementById(buttonId);
  if (btn) {
    btn.addEventListener("click", function () {
      $('#ForReleasingModal').modal('hide');

      const formData = new FormData();

      // Header Data
      formData.append('request_id', $('#req_id').val());
      formData.append('employee_id', $('#emp_id').val());
      formData.append('req_coe_type', $('#req_coe_type').val());
      formData.append('request_reason', $('#req_reason').val());
      formData.append('date_needed', $('#date_needed').val());
      formData.append('request_format', $('#receivingFormat').val());
      formData.append('requested_date', new Date().toISOString().split('T')[0]);
      formData.append('requested_time', new Date().toLocaleTimeString('en-GB'));
      formData.append('requested_by', $('#requested_by').val());
      formData.append('tagged_by', $('#current_user').val());
      formData.append('request_status', 'FOR RELEASING');

      formData.append('remarks', $('#forreleasing_remarks').val());
      const req_Id = $('#req_id').val();
      const user_Id = $('#requested_by').val();

      $.ajax({
        url: 'updates/request_coe_forReleasing_update.php',
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
            title: "Tagged!",
            text: "Request Status: FOR RELEASING",
            icon: "success",
            confirmButtonText: "OK",
            confirmButtonColor: "#0dcaf0",
            allowOutsideClick: false,
            allowEscapeKey: false
          }).then((result) => {
            if (result.isConfirmed) {
              const page = 'request_coe_hr_list.php';
              fetch(page)
                .then(response => response.text())
                .then(data => {
                  document.getElementById('main-content').innerHTML = data;
                })
                .catch(error => {
                  document.getElementById('main-content').innerHTML = '<p>Error loading content.</p>';
                  console.error('Error:', error);
                });
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
  }
}


// Released

function COE_Released(buttonId) {
  const btn = document.getElementById(buttonId);
  if (btn) {
    btn.addEventListener("click", function () {
      $('#ReleasedModal').modal('hide');

      const formData = new FormData();

      // Header Data
      formData.append('request_id', $('#req_id').val());
      formData.append('employee_id', $('#emp_id').val());
      formData.append('req_coe_type', $('#req_coe_type').val());
      formData.append('request_reason', $('#req_reason').val());
      formData.append('date_needed', $('#date_needed').val());
      formData.append('request_format', $('#receivingFormat').val());
      formData.append('requested_date', new Date().toISOString().split('T')[0]);
      formData.append('requested_time', new Date().toLocaleTimeString('en-GB'));
      formData.append('requested_by', $('#requested_by').val());
      formData.append('tagged_by', $('#current_user').val());
      formData.append('request_status', 'RELEASED');

      formData.append('remarks', $('#released_remarks').val());
      const req_Id = $('#req_id').val();
      const user_Id = $('#requested_by').val();

      $.ajax({
        url: 'updates/request_coe_Released_update.php',
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
            title: "Tagged!",
            text: "Request Status: RELEASED",
            icon: "success",
            confirmButtonText: "OK",
            confirmButtonColor: "#198754",
            allowOutsideClick: false,
            allowEscapeKey: false
          }).then((result) => {
            if (result.isConfirmed) {
              const page = 'request_coe_hr_list.php';
              fetch(page)
                .then(response => response.text())
                .then(data => {
                  document.getElementById('main-content').innerHTML = data;
                })
                .catch(error => {
                  document.getElementById('main-content').innerHTML = '<p>Error loading content.</p>';
                  console.error('Error:', error);
                });
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
  }
}


// On Hold

function COE_OnHold(buttonId) {
  const btn = document.getElementById(buttonId);
  if (btn) {
    btn.addEventListener("click", function () {
      $('#OnHoldModal').modal('hide');

      const formData = new FormData();

      // Header Data
      formData.append('request_id', $('#req_id').val());
      formData.append('employee_id', $('#emp_id').val());
      formData.append('req_coe_type', $('#req_coe_type').val());
      formData.append('request_reason', $('#req_reason').val());
      formData.append('date_needed', $('#date_needed').val());
      formData.append('request_format', $('#receivingFormat').val());
      formData.append('requested_date', new Date().toISOString().split('T')[0]);
      formData.append('requested_time', new Date().toLocaleTimeString('en-GB'));
      formData.append('requested_by', $('#requested_by').val());
      formData.append('tagged_by', $('#current_user').val());
      formData.append('request_status', 'ON HOLD');

      formData.append('remarks', $('#onhold_remarks').val());
      const req_Id = $('#req_id').val();
      const user_Id = $('#requested_by').val();

      $.ajax({
        url: 'updates/request_coe_onhold_update.php',
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
            title: "Tagged!",
            text: "Request Status: ON HOLD",
            icon: "success",
            confirmButtonText: "OK",
            confirmButtonColor: "#6c757d",
            allowOutsideClick: false,
            allowEscapeKey: false
          }).then((result) => {
            if (result.isConfirmed) {
              const page = 'request_coe_hr_list.php';
              fetch(page)
                .then(response => response.text())
                .then(data => {
                  document.getElementById('main-content').innerHTML = data;
                })
                .catch(error => {
                  document.getElementById('main-content').innerHTML = '<p>Error loading content.</p>';
                  console.error('Error:', error);
                });
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
  }
}

//GENERATE PRINT

function COE_TravelPrint(buttonId) {
  const btn = document.getElementById(buttonId);
  if (btn) {
    btn.addEventListener("click", function () {
      const requestIdField = document.getElementById('req_id');
      const requestId = requestIdField ? requestIdField.value : '';
      const CoeTypeField = document.getElementById('req_coe_type');
      const CoeType = CoeTypeField ? CoeTypeField.value : '';

      if (CoeType === 'TRAVEL') {
        if (requestId) {
          window.open('print/request_coe_travel.php?request_id=' + encodeURIComponent(requestId), '_blank');
        } else {
          alert('Request ID is missing.');
        }
      }

      if (CoeType === 'BENEFIT CLAIM' || CoeType === 'BENEFIT CLAIM WITH COMPENSATION') {
        if (requestId) {
          window.open('print/request_coe_benefitclaim.php?request_id=' + encodeURIComponent(requestId), '_blank');
        } else {
          alert('Request ID is missing.');
        }
      }

      if (CoeType === 'FINANCIAL') {
        if (requestId) {
          window.open('print/request_coe_financial.php?request_id=' + encodeURIComponent(requestId), '_blank');
        } else {
          alert('Request ID is missing.');
        }
      }

      if (CoeType === 'TRAINING/EDUCATIONAL') {
        if (requestId) {
          window.open('print/request_coe_training.php?request_id=' + encodeURIComponent(requestId), '_blank');
        } else {
          alert('Request ID is missing.');
        }
      }


    });
  }
}

//VIEW LEAVE
//VIEW LEAVE

function COE_ViewLeave(buttonId) {
  const btn = document.getElementById(buttonId);
  if (btn) {
    btn.addEventListener("click", function () {
      const employeeId = document.getElementById('emp_id').value.trim();
      const startDate = document.getElementById('dateFrom').value;
      const endDate = document.getElementById('dateTo').value;

      if (!employeeId || !startDate || !endDate) {
        alert('Employee ID and dates must be provided.');
        return;
      }

      fetch(`fetch/request_coe_viewleave.php?employee_id=${encodeURIComponent(employeeId)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`)
        .then(response => response.json())
        .then(data => {
          const tbody = document.querySelector('#leaveTable tbody');
          tbody.innerHTML = '';

          if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">No leave records found.</td></tr>';
          } else {
            data.forEach(leave => {
              const row = document.createElement('tr');
              // Updated this part to use an onclick event for the button
              row.innerHTML = `
              <td>${leave.leaveid}</td>
              <td class="text-center">${leave.leavedate}</td>
              <td class="text-center">${leave.leavedurationselection}</td>
              <td class="text-center">${leave.leavetype}</td>
              <td class="text-start">${leave.reason}</td>
              <td class="text-center">${leave.status}</td>
              <td class="text-center">
                <button class="btn btn-sm btn-outline-dark rounded-3 px-3" onclick="viewLeavePDF('${leave.leaveid}')">
                  View
                </button>
              </td>
            `;
              tbody.appendChild(row);
            });
          }

          const leaveModal = new bootstrap.Modal(document.getElementById('leaveModal'));
          leaveModal.show();
        })
        .catch(error => {
          console.error('Error fetching leave data:', error);
        });
    });
  }
}

// New function to open the PDF in a new tab
function viewLeavePDF(leaveId) {
  const employeeId = document.getElementById('emp_id').value.trim();
  const startDate = document.getElementById('dateFrom').value;
  const endDate = document.getElementById('dateTo').value;

  if (!employeeId || !startDate || !endDate) {
    showAlert('Employee ID and travel dates must be provided to view leave details.');
    return;
  }

  const url = `print/view_leave_details.php?employee_id=${encodeURIComponent(employeeId)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
  window.open(url, '_blank');
}


// -----BENIFIT CLAIM FORM-----


// change status

function BENIFITCLAIM_COMPENSATION(buttonId) {
  const btn = document.getElementById(buttonId);
  const coeTypeInput = document.getElementById('req_coe_type');

  if (btn && coeTypeInput) {
    btn.addEventListener("change", function () {
      if (this.checked) {
        coeTypeInput.value = "BENEFIT CLAIM WITH COMPENSATION";
      } else {
        coeTypeInput.value = "BENEFIT CLAIM";
      }
    });
  }
}

//compensation details

function EnableCompensationEditing(buttonId) {
  const btn = document.getElementById(buttonId);
  const content = document.getElementById('compensation_details');
  const updateBtn = document.getElementById('btn_update_compensation');
  const cancelBtn = document.getElementById('btn_canceledit_compensation');
  const coeTypeVal = document.getElementById('req_coe_type').value;

  if (btn && content && updateBtn && cancelBtn) {
    btn.addEventListener("click", function () {
      if (coeTypeVal === 'TRAINING/EDUCATIONAL') {
        const emp_title = document.getElementById('emp_title');
        emp_title.disabled = false;
      }
      content.disabled = false;
      content.contentEditable = "true";
      content.style.backgroundColor = '';
      btn.style.display = 'none';
      updateBtn.style.display = 'inline-block';
      cancelBtn.style.display = 'inline-block';
    });

    cancelBtn.addEventListener("click", function () {
      content.innerHTML = content.getAttribute('data-original');
      if (coeTypeVal === 'TRAINING/EDUCATIONAL') {
        const emp_title = document.getElementById('emp_title');
        emp_title.value = emp_title.getAttribute('data-original');
        emp_title.disabled = true;
      }
      content.disabled = true;
      content.contentEditable = "false";
      content.style.backgroundColor = '#e9ecef';
      btn.style.display = 'inline-block';
      updateBtn.style.display = 'none';
      cancelBtn.style.display = 'none';
    });

    updateBtn.addEventListener("click", function () {
      if (coeTypeVal === 'TRAINING/EDUCATIONAL') {
        const emp_title = document.getElementById('emp_title');
        emp_title.disabled = true;
      }
      content.disabled = true;
      content.contentEditable = "false";
      content.style.backgroundColor = '#e9ecef';
      btn.style.display = 'inline-block';
      updateBtn.style.display = 'none';
      cancelBtn.style.display = 'none';
    });
  }
}



function COE_UpdateCompensation(buttonId) {
  const btn = document.getElementById(buttonId);
  if (btn) {
    btn.addEventListener("click", function () {
      const requestId = document.getElementById('req_id').value;
      const compensation = document.getElementById('compensation_details').innerHTML;

      const formData = new FormData();
      formData.append('request_id', requestId);
      formData.append('compensation_details', compensation);

      fetch('updates/request_coe_benefitclaim_compensation_update.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.text())
        .then(data => {
          if (data === 'success') {
            showSuccess('Compensation details updated.');
            document.getElementById('compensation_details').disabled = true;
            document.getElementById('btn_update_compensation').style.display = 'none';
            document.getElementById('btn_edit_compensation').style.display = 'inline-block';
            document.getElementById('btn_canceledit_compensation').style.display = 'none';
          } else {
            showAlert('Update failed: ' + data);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred.');
        });
    });
  }
}

// FINANCIAL

function COE_UpdatePurpose(buttonId) {
  const btn = document.getElementById(buttonId);
  if (btn) {
    btn.addEventListener("click", function () {
      const requestId = document.getElementById('req_id').value;
      const compensation = document.getElementById('compensation_details').innerHTML;

      const formData = new FormData();
      formData.append('request_id', requestId);
      formData.append('compensation_details', compensation);

      fetch('updates/request_coe_financial_purposes_update.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.text())
        .then(data => {
          if (data === 'success') {
            showSuccess('Compensation details updated.');
            document.getElementById('compensation_details').disabled = true;
            document.getElementById('btn_update_compensation').style.display = 'none';
            document.getElementById('btn_edit_compensation').style.display = 'inline-block';
            document.getElementById('btn_canceledit_compensation').style.display = 'none';
          } else {
            showAlert('Update failed: ' + data);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred.');
        });
    });
  }
}


// -----COE TRAINING/EDUCATIONAL FORM-----

function COE_UpdateTitleAndPurposes(buttonId) {
  const btn = document.getElementById(buttonId);
  if (btn) {
    btn.addEventListener("click", function () {
      const requestId = document.getElementById('req_id').value;
      const compensation = document.getElementById('compensation_details').innerHTML;
      const emp_title = document.getElementById('emp_title').value;


      const formData = new FormData();
      formData.append('request_id', requestId);
      formData.append('compensation_details', compensation);
      formData.append('emp_title', emp_title);


      fetch('updates/request_coe_training_purposes_update.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.text())
        .then(data => {
          if (data === 'success') {
            showSuccess('Compensation details updated.');
            document.getElementById('emp_title').disabled = true;
            document.getElementById('compensation_details').disabled = true;
            document.getElementById('btn_update_compensation').style.display = 'none';
            document.getElementById('btn_edit_compensation').style.display = 'inline-block';
            document.getElementById('btn_canceledit_compensation').style.display = 'none';
          } else {
            showAlert('Update failed: ' + data);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred.');
        });
    });
  }
}


// VAULT PASSWORD

let vaultModal;

function Benefit_Vault_Password(buttonId, employeeId, requestId) {
  const btn = document.getElementById(buttonId);
  const vaultModalElement = document.getElementById('vaultModal');
  const vaultPasswordInput = document.getElementById('vaultPassword');
  const vaultError = document.getElementById('vault_error');
  const unlockBtn = document.getElementById('vault_unlock_btn');

  // Create modal instance only once
  if (!vaultModal) {
    vaultModal = new bootstrap.Modal(vaultModalElement, {
      backdrop: 'static',
      keyboard: false
    });
  }

  // Only bind unlock handler once
  if (!unlockBtn.dataset.bound) {
    unlockBtn.addEventListener('click', () => {
      const password = vaultPasswordInput.value;

      fetch('functions/vault_login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `employee_id=${encodeURIComponent(employeeId)}&password=${encodeURIComponent(password)}`
      })
        .then(res => res.text())
        .then(result => {
          if (result.trim() !== 'success') throw new Error('Invalid password');

          return fetch('fetch/coe/benefits_compensation_fetch.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `request_id=${encodeURIComponent(requestId)}`
          });
        })
        .then(res => {
          if (!res.ok) throw new Error('Failed to load compensation data');
          return res.text();
        })
        .then(data => {
          document.getElementById('div_hide_compensation').classList.remove('d-none');
          document.getElementById('div_view_compensation').classList.add('d-none');
          document.getElementById('compensation_wrapper').classList.remove('d-none');
          document.getElementById('compensation_wrapper').innerHTML = `
            <div class="mb-2">
              <label class="form-label small">
                Compensation Details<span class="text-danger"> *</span><span class="text-muted fst-italic">(Encoded by HR)</span>
              </label>
              <div class="form-control rounded-4" style="min-height: 80px;" contenteditable="false">${data}</div>
            </div>`;

          // ✅ Correctly close the modal
          vaultModal.hide();


          // ✅ Show SweetAlert success
          Swal.fire({
            icon: 'success',
            title: 'Vault Unlocked',
            text: 'Compensation details are now visible.',
            confirmButtonColor: '#198754', // Bootstrap success green
            timer: 2000,
            showConfirmButton: false
          });
        })
        .catch(() => {
          vaultError.classList.remove('d-none');
        });
    });

    unlockBtn.dataset.bound = 'true';
  }

  // Attach click to show modal
  if (btn) {
    btn.addEventListener('click', () => {
      vaultPasswordInput.value = '';
      vaultError.classList.add('d-none');
      vaultModal.show();
    });
  }
}


function Hide_Compensation() {
  // Hide the compensation content
  document.getElementById('div_hide_compensation').classList.add('d-none');
  document.getElementById('div_view_compensation').classList.remove('d-none');
  document.getElementById('compensation_wrapper').classList.add('d-none');
  document.getElementById('compensation_wrapper').innerHTML = '';
}
