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

function onDepartmentChange() {
  const selectedDept = document.getElementById('departmentFilterDropdown').value;
  loadAreas(selectedDept);

  const defaultArea = document.getElementById('area_data').value;
  const areaSelect = document.getElementById('areaFilterDropdown');

  if (defaultArea) {
    const areaObserver = new MutationObserver(() => {
      const optionToSelect = areaSelect.querySelector(`option[value="${defaultArea}"]`);
      if (optionToSelect) {
        areaSelect.value = defaultArea;
        loadEmployees(selectedDept, defaultArea);
        areaObserver.disconnect();
      }
    });
    areaObserver.observe(areaSelect, { childList: true });
  }
}

function onAreaChange() {
  const selectedArea = document.getElementById('areaFilterDropdown').value;
  const selectedDept = document.getElementById('departmentFilterDropdown').value;
  loadEmployees(selectedDept, selectedArea);
}

function onMonthYearChange() {
  const selectedArea = document.getElementById('areaFilterDropdown').value;
  const selectedDept = document.getElementById('departmentFilterDropdown').value;
  loadEmployees(selectedDept, selectedArea);
}

function loadEmployees(dept, area) {

  const monthDropdown = document.getElementById("monthDropdown");
  const yearDropdown = document.getElementById("yearDropdown");
  const month = monthDropdown.value;
  const year = yearDropdown.value;

  console.log(`Department: ${dept}, Area: ${area}, Month: ${month}, Year: ${year}`);

  fetch(`../fetch/wtm/wtm_schedule_employeelist.php?department=${encodeURIComponent(dept)}&area=${encodeURIComponent(area)}&month=${encodeURIComponent(month)}&year=${encodeURIComponent(year)}`)
    .then(response => {
      if (!response.ok) throw new Error('Network response was not ok: ' + response.status);
      return response.json();
    })
    .then(data => {
      const tbody = document.getElementById('employees_tbody');
      tbody.innerHTML = '';

      if (!Array.isArray(data) || data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted">No employees found.</td></tr>`;
        return;
      }

      data.forEach(row => {
        const employeeid = row.employeeid;
        const tr = document.createElement('tr');
        tr.innerHTML = `
        <td class="text-center" style="width:10%;">
          <img src="${row.image && row.image.trim() ? row.image : '../assets/imgs/user_default.png'}" 
              class="img-fluid rounded-circle" style="max-width:30px;">
        </td>
        <td class="text-center" style="width:10%;">${employeeid}</td>
        <td class="text-start text-wrap" style="width:35%;">${row.employeename}</td>
        <td class="text-start text-wrap" style="width:20%;">${row.department}</td>
        <td class="text-start text-wrap" style="width:15%;">${row.area}</td>
        <td class="text-start text-wrap" style="width:15%;">${row.position}</td>

        <td class="text-center" style="width:10%;">
            <button class="btn btn-sm btn-dark mb-1" 
                    data-employeeid="${employeeid}" 
                    onclick="ViewSchedule('${employeeid}', '${month}', '${year}')">
                <i class="bi bi-eye"></i>
            </button>

            ${row.Sched_Status === 'EDIT'
            ? `<button class="btn btn-sm btn-warning mb-1" 
                          data-employeeid="${employeeid}" 
                          onclick="EditSchedule('${employeeid}', '${month}', '${year}')">
                      <i class="bi bi-pencil-square"></i>
                  </button>`
            : `<button class="btn btn-sm btn-success mb-1" 
                          data-employeeid="${employeeid}" 
                          onclick="setSchedule('${employeeid}', '${month}', '${year}')">
                      <i class="bi bi-calendar"></i>
                  </button>`
          }
        </td>

      `;
        tbody.appendChild(tr);
      });
    })
    .catch(err => console.error('Fetch error:', err));
}

function setSchedule(employeeId, month, year) {
  if (!employeeId) return;

  $.ajax({
    url: '../fetch/wtm/wtm_employee_details.php',
    type: 'POST',
    data: { employee_id: employeeId },
    dataType: 'json', // Expect JSON response
    success: function (response) {
      // Create a form dynamically to POST data to the next page
      var form = $('<form>', {
        method: 'POST',
        action: 'shiftschedule_set_schedule.php'
      });

      form.append($('<input>', { type: 'hidden', name: 'employeeid', value: employeeId }));
      form.append($('<input>', { type: 'hidden', name: 'month', value: month }));
      form.append($('<input>', { type: 'hidden', name: 'year', value: year }));
      form.appendTo('body').submit();

    },
    error: function (xhr, status, error) {
      console.error("AJAX Error:", status, error);
    }
  });
}

function EditSchedule(employeeId, month, year) {
  if (!employeeId) return;

  $.ajax({
    url: '../fetch/wtm/wtm_employee_details.php',
    type: 'POST',
    data: { employee_id: employeeId },
    dataType: 'json',
    success: function (response) {
      var form = $('<form>', {
        method: 'POST',
        action: 'shiftschedule_edit_schedule.php'
      });

      form.append($('<input>', { type: 'hidden', name: 'employeeid', value: employeeId }));
      form.append($('<input>', { type: 'hidden', name: 'month', value: month }));
      form.append($('<input>', { type: 'hidden', name: 'year', value: year }));
      form.appendTo('body').submit();

    },
    error: function (xhr, status, error) {
      console.error("AJAX Error:", status, error);
    }
  });
}

function ViewSchedule(employeeId, month, year) {
  if (!employeeId) return;

  $.ajax({
    url: '../fetch/wtm/wtm_employee_details.php',
    type: 'POST',
    data: { employee_id: employeeId },
    dataType: 'json',
    success: function (response) {
      var form = $('<form>', {
        method: 'POST',
        action: 'shiftschedule_view_schedule.php'
      });

      form.append($('<input>', { type: 'hidden', name: 'employeeid', value: employeeId }));
      form.append($('<input>', { type: 'hidden', name: 'month', value: month }));
      form.append($('<input>', { type: 'hidden', name: 'year', value: year }));
      form.appendTo('body').submit();

    },
    error: function (xhr, status, error) {
      console.error("AJAX Error:", status, error);
    }
  });
}