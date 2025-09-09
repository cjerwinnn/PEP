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
            alertDiv.classList.remove('show');
            alertDiv.classList.add('fade');
            alertDiv.classList.add('hide');
            setTimeout(() => {
                alertDiv.remove();
            }, 200);
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
        alertDiv.classList.remove('show');
        alertDiv.classList.add('hide');
        setTimeout(() => {
            alertDiv.remove();
        }, 150);
    }, 2000);
}

// Declined

document.getElementById('cancel_request').addEventListener("click", function (event) {
    event.preventDefault(); // prevent form auto-submit

    const cancel_remarks = document.getElementById('cancel_remarks');

    if (!cancel_remarks.value) {
        showAlert('Cancellation remarks is required.');
        cancel_remarks.focus();
        this.disabled = false;
        this.textContent = 'Cancel application';
        return;
    }

    const formData = new FormData();
    // Header Data
    const overtimeId = document.getElementById('overtimeid_data').value;
    const currentUserId = document.getElementById('current_user').value;
    const requestor_EmployeeID = document.getElementById('requestorempid_data').value;

    formData.append('request_id', overtimeId);
    formData.append('requestor_EmployeeID', requestor_EmployeeID);
    formData.append('approved_overtime_hours', 0);
    formData.append('tagged_by', currentUserId);
    formData.append('request_status', 'CANCELLED');
    formData.append('cancel_remarks', $('#cancel_remarks').val());

    $.ajax({
        url: '../updates/wtm/wtm_overtime_cancel_update.php',
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
                title: "Cancelled!",
                text: "Request Cancelled.",
                icon: "success",
                confirmButtonText: "OK",
                confirmButtonColor: "#ff1e00ff",
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                if (result.isConfirmed) {
                    const page = 'wtm_mydtr.php';
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