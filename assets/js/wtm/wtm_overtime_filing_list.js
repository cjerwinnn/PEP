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
    }, 2500);
  }
}


function onMonthYearChange() {
  loadEmployees();
}

function loadEmployees() {

   const employeeid = document.getElementById("current_user").value;

  const monthDropdown = document.getElementById("monthDropdown");
  const yearDropdown = document.getElementById("yearDropdown");
  const month = monthDropdown.value;
  const year = yearDropdown.value;

  fetch(`../fetch/wtm/wtm_overtime_filing_list.php?employeeid=${encodeURIComponent(employeeid)}&month=${encodeURIComponent(month)}&year=${encodeURIComponent(year)}`)
    .then(response => {
      if (!response.ok) throw new Error('Network response was not ok: ' + response.status);
      return response.json();
    })
    .then(data => {
      const tbody = document.getElementById('employees_tbody');
      tbody.innerHTML = '';

      if (!Array.isArray(data) || data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="10" class="text-center text-muted">No employees found.</td></tr>`;
        return;
      }

      data.forEach(row => {
        const employeeid = row.employeeid;
        const tr = document.createElement('tr');
        tr.innerHTML = `
        <td class="text-start text-wrap" style="width:20%;">${row.overtimeid}</td>
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
