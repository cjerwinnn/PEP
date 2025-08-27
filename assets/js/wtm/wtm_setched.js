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

// === GLOBALS ===
let selectedDates = [];
let lastClickedDate = null;

let selectedShift = null;
let shiftStart = null;
let shiftEnd = null;
let shiftColor = null;

let DataToSave = [];

let FinalData = {};

const empInput = document.getElementById('setsched_employeeid');

// Check if it exists
const employeeId = empInput ? empInput.value.trim() : '';

FinalData = {
  employeeid: employeeId || '',
  schedules: DataToSave
};

// === INIT CALENDAR ===
function initCalendar() {
  const allCells = document.querySelectorAll(".calendar td[data-date]");
  allCells.forEach(td => {
    const date = td.dataset.date;
    const holidayName = td.dataset.holidayname || "";
    // Fill default (shiftcode = "NS")
    DataToSave.push({
      date: date,
      shiftcode: "NS",   // default
      holidayname: holidayName
    });
  });

  console.log("Initial DataToSave with NS:", FinalData);
}

// Call this once after rendering calendar
initCalendar();

// === SELECT DATE FUNCTION ===
function selectDate(event) {
  const td = event.currentTarget;
  if (td.classList.contains('disabled-day')) return;

  const date = td.dataset.date;  // still need dataset just to know which date cell was clicked
  const ctrlPressed = event.ctrlKey;
  const shiftPressed = event.shiftKey;

  if (!ctrlPressed && !shiftPressed) {
    document.querySelectorAll('.calendar td.selected').forEach(cell => cell.classList.remove('selected'));
    selectedDates = [date];
    td.classList.add('selected');
  } else if (ctrlPressed) {
    if (selectedDates.includes(date)) {
      selectedDates = selectedDates.filter(d => d !== date);
      td.classList.remove('selected');
    } else {
      selectedDates.push(date);
      td.classList.add('selected');
    }
  } else if (shiftPressed && lastClickedDate) {
    const allCells = Array.from(document.querySelectorAll('.calendar td[data-date]'));
    const startIndex = allCells.findIndex(c => c.dataset.date === lastClickedDate);
    const endIndex = allCells.findIndex(c => c.dataset.date === date);
    const [from, to] = startIndex < endIndex ? [startIndex, endIndex] : [endIndex, startIndex];

    for (let i = from; i <= to; i++) {
      const cell = allCells[i];
      if (cell.classList.contains('disabled-day')) continue;
      const d = cell.dataset.date;
      if (!selectedDates.includes(d)) selectedDates.push(d);
      cell.classList.add('selected');
    }
  }

  lastClickedDate = date;
  document.getElementById('selectedDates').textContent = selectedDates.join(', ') || 'None';

  if (selectedShift) applyShiftToDates(selectedShift, shiftStart, shiftEnd, shiftColor);
}

// === RADIO BUTTONS ===
document.querySelectorAll("input[name='schedule_select']").forEach(radio => {
  radio.addEventListener('change', function () {
    selectedShift = this.value;
    const row = this.closest('tr');
    shiftStart = row.dataset.start;
    shiftEnd = row.dataset.end;
    shiftColor = row.dataset.color || "#0d6efd";

    applyShiftToDates(selectedShift, shiftStart, shiftEnd, shiftColor);
  });
});

// === APPLY SHIFT ===
function applyShiftToDates(code, start, end, color) {
  selectedDates.forEach(date => {
    const td = document.querySelector(`.calendar td[data-date='${date}']`);
    if (!td || td.classList.contains('disabled-day')) return;

    // --- maintain DataToSave ---
    let holidayName = td.dataset.holidayname || "";
    let existing = DataToSave.find(d => d.date === date);
    if (existing) {
      existing.shiftcode = code;
      existing.holidayname = holidayName;
    } else {
      // --- update DataToSave (already initialized) ---
      let existing = DataToSave.find(d => d.date === date);
      if (existing) {
        existing.shiftcode = code;
        existing.holidayname = holidayName;
      }
    }

    // --- UI updates ---
    let labelText = `[${code}]`;
    if (start && end) labelText += ` ${start} - ${end}`;
    else if (start) labelText += ` ${start}`;
    else if (end) labelText += ` ${end}`;

    let span = td.querySelector('.shift-label');
    if (!span) {
      span = document.createElement('span');
      span.classList.add('shift-label');
      span.style.display = 'block';
      span.style.fontSize = '0.9rem';
      span.style.marginTop = '4px';
      span.style.color = 'white';
      span.style.fontWeight = 'bold';
      td.appendChild(span);
    }
    span.textContent = labelText;

    td.style.backgroundColor = color;
    td.style.color = "#fff";
  });

  console.log("DataToSave:", FinalData); // DEBUG
}

// ESC key
document.addEventListener('keydown', function (event) {
  if (event.key === "Escape") {
    document.querySelectorAll('.calendar td.selected').forEach(cell => {
      cell.classList.remove('selected');
    });

    selectedDates = []; // clear only selectedDates
    document.getElementById('selectedDates').textContent = 'None';
  }
});

//Reset Shift

document.getElementById('resetShiftsBtn').addEventListener('click', function () {
  Swal.fire({
    title: '<span style="font-size: 1.3rem;">Do you want to reset all shifts?</span>',
    showDenyButton: true,
    confirmButtonText: 'Yes',
    confirmButtonColor: "#dc3545", // red for reset
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
      // --- Clear all calendar cells ---
      document.querySelectorAll('.calendar td[data-date]').forEach(td => {
        td.removeAttribute('data-shift');
        td.style.backgroundColor = "";
        td.style.color = "";
        const span = td.querySelector('.shift-label');
        if (span) span.remove();
      });

      // --- Clear tracking data ---
      selectedDates = [];
      lastClickedDate = null;
      selectedShift = null;
      shiftStart = null;
      shiftEnd = null;
      shiftColor = null;
      DataToSave = [];
      FinalData = {
        employeeid: document.getElementById('setsched_employeeid').value || '',
        schedules: DataToSave
      };
      initCalendar();

      // --- Clear UI info ---
      document.getElementById('selectedDates').textContent = 'None';

      // --- Uncheck radio buttons ---
      document.querySelectorAll("input[name='schedule_select']").forEach(radio => {
        radio.checked = false;
      });

      // --- Feedback ---
      Swal.fire({
        title: '<span style="font-size: 1.6rem;">Reset Done!</span>',
        html: '<span style="font-size: 1.3rem;">All shift schedules have been cleared.</span>',
        icon: "success",
        confirmButtonText: "OK",
        allowOutsideClick: false,
        allowEscapeKey: false
      });
    }
  });
});



//SAVE
document.getElementById('applyShiftBtn').addEventListener('click', function () {

  // Prevent spamming
  if (this.disabled) return;
  this.disabled = true;
  this.textContent = 'Applying...';

  let employeeId = document.getElementById('setsched_employeeid').value || FinalData.employeeid;
  FinalData.employeeid = employeeId;

  const nsDates = FinalData.schedules
    .filter(sched => {
      const td = document.querySelector(`.calendar td[data-date='${sched.date}']`);
      return sched.shiftcode === "NS" && !td.classList.contains("disabled-day");
    })
    .map(sched => sched.date);

  if (nsDates.length > 0) {
    const formattedDates = nsDates.map(d => {
      const dateObj = new Date(d);
      const options = { month: 'short', day: '2-digit', year: 'numeric' };
      return dateObj.toLocaleDateString('en-US', options);
    });

    showAlert(
      ` There are ${nsDates.length} date(s) with NO SCHEDULE assigned:<br>` +
      formattedDates.join("<br>")
    );
    this.disabled = false;
    this.textContent = 'Apply Shift Schedule';
    return;
  }

  Swal.fire({
    title: '<span style="font-size: 1.3rem;">Do you want to apply the shift schedule?</span>',
    showDenyButton: true,
    showCancelButton: false,
    confirmButtonText: 'Yes',
    confirmButtonColor: "#198754",
    denyButtonText: 'No',
    allowOutsideClick: false,
    allowEscapeKey: false,
    customClass: {
      actions: 'my-actions',
      cancelButton: 'order-1 right-gap',
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
      let params = "employeeid=" + encodeURIComponent(FinalData.employeeid);
      FinalData.schedules.forEach(sched => {
        params += "&dates[]=" + encodeURIComponent(sched.date);
        params += "&shiftcodes[]=" + encodeURIComponent(sched.shiftcode);
        params += "&holidays[]=" + encodeURIComponent(sched.holidayname || "");
      });

      let xhr = new XMLHttpRequest();
      xhr.open("POST", "../inserts/wtm/wtm_setschedule_insert.php", true);
      xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
          Swal.fire({
            title: '<span style="font-size: 1.6rem;">Submitted!</span>',
            html: '<span style="font-size: 1.3rem;">Schedule has been applied.</span>',
            icon: "success",
            confirmButtonText: "OK",
            allowOutsideClick: false,
            allowEscapeKey: false
          }).then(() => {
            window.location.href = 'shiftschedule_employee_list.php';
          });
        }
      };
      xhr.send(params);

    } else {
      this.disabled = false;
      this.textContent = 'Apply Shift Schedule';
    }
  });
})
