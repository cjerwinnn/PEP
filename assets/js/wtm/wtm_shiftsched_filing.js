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

  const monthText = monthDropdown.options[monthDropdown.selectedIndex].text;

  document.getElementById("monthYearHeader").textContent = monthText + " " + yearDropdown.value;

  fetch(`../fetch/wtm/wtm_schedule_filing_list.php?employeeid=${encodeURIComponent(employeeid)}&month=${encodeURIComponent(month)}&year=${encodeURIComponent(year)}`)
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

        const currentshiftdisplay = row.currentshiftsched == '00:00 - 00:00' ? '' : row.currentshiftsched;
        const newshiftdisplay = row.newshiftsched == '00:00 - 00:00' ? '' : row.newshiftsched;

        tr.innerHTML = `
        <td class="text-center text-start text-wrap" style="width:40%;">${row.requestid}</td>
        <td class="text-center text-start text-wrap" style="width:10%;">${row.shiftdate}</td>
        <td class="text-center text-start text-wrap" style="width:15%;">${row.currentshiftcode}</td>
        <td class="text-center text-start text-wrap" style="width:15%;">${currentshiftdisplay}</td>
        <td class="text-center text-start text-wrap" style="width:15%;">${row.newshiftcode}</td>
        <td class="text-center text-start text-wrap" style="width:15%;">${newshiftdisplay}</td>
        <td class="text-center text-start text-wrap" style="width:15%;">${row.status}</td>
        <td class="text-center text-start text-wrap" style="width:15%;">${row.datecreated}</td>
        <td class="text-center text-start text-wrap" style="width:15%;">${row.timecreated}</td>

        <td class="text-center" style="width:10%;">
            <button class="btn btn-sm btn-dark mb-1" 
                    data-employeeid="${employeeid}" 
                    onclick="ViewSchedule('${employeeid}', '${month}', '${year}')">
                <i class="bi bi-eye"></i>
            </button>
        </td>`;

        tbody.appendChild(tr);
      });
    })
    .catch(err => console.error('Fetch error:', err));
}