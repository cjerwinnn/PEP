// add hovered class to selected list item
let list = document.querySelectorAll(".navigation li");

function activeLink() {
  list.forEach((item) => {
    item.classList.remove("hovered");
  });
  this.classList.add("hovered");
}

list.forEach((item) => item.addEventListener("mouseover", activeLink));

// Menu Toggle
let toggle = document.querySelector(".toggle");
let navigation = document.querySelector(".navigation");
let main = document.querySelector(".main");

toggle.onclick = function () {
  navigation.classList.toggle("active");
  main.classList.toggle("active");
};


// Initial load of dashboard.php into main-content
document.addEventListener('DOMContentLoaded', () => {
  fetch('dashboard/community_wall.php')
    .then(response => response.text())
    .then(data => {
      document.getElementById('main-content').innerHTML = data;
    })
    .catch(error => {
      document.getElementById('main-content').innerHTML = '<p>Error loading content.</p>';
      console.error('Error:', error);
    });

  // Then bind your nav links click event
  document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      const page = this.getAttribute('data-page');
      fetch(page)
        .then(response => response.text())
        .then(data => {
          document.getElementById('main-content').innerHTML = data;
        })
        .catch(error => {
          document.getElementById('main-content').innerHTML = '<p>Error loading content.</p>';
          console.error('Error:', error);
        });
    });
  });

  // Handle logout to clear chat interval
  const logoutLink = document.getElementById('logout-link');
  if (logoutLink) {
    logoutLink.addEventListener('click', function (e) {
      e.preventDefault(); // Stop the link from redirecting immediately

      // Clear the chat refresh interval if it exists
      if (typeof inboxRefreshInterval !== 'undefined' && inboxRefreshInterval) {
        clearInterval(inboxRefreshInterval);
        inboxRefreshInterval = null; // Clean up the variable
      }

      // Now, proceed to the logout page
      window.location.href = this.href;
    });
  }
});


document.querySelectorAll('.nav-link').forEach(link => {
  link.addEventListener('click', function (e) {
    e.preventDefault();
    const page = this.getAttribute('data-page');
    fetch(page)
      .then(response => response.text())
      .then(data => {

        document.getElementById('main-content').innerHTML = data;
      })
      .catch(error => {
        document.getElementById('main-content').innerHTML = '<p>Error loading content.</p>';
        console.error('Error:', error);
      });
  });
});

function loadPage(page) {
  const mainContent = document.getElementById('main-content');

  // Start with content hidden and shifted
  mainContent.classList.add('loading');

  fetch(page)
    .then(response => response.text())
    .then(data => {
      mainContent.innerHTML = data;

      // Trigger slide-in effect by removing the class after repaint
      requestAnimationFrame(() => {
        mainContent.classList.remove('loading');
      });
    })
    .catch(error => {
      mainContent.innerHTML = '<p class="text-danger">Error loading page.</p>';
      console.error('Error:', error);
      mainContent.classList.remove('loading');
    });
}

function Maintenance_COE_checklist(page) {
  const mainContent = document.getElementById('main-content');

  // Start with content hidden and shifted
  mainContent.classList.add('loading');

  fetch(page)
    .then(response => response.text())
    .then(data => {
      mainContent.innerHTML = data;

      // Trigger slide-in effect by removing the class after repaint
      requestAnimationFrame(() => {
        mainContent.classList.remove('loading');
      });
    })
    .catch(error => {
      mainContent.innerHTML = '<p class="text-danger">Error loading page.</p>';
      console.error('Error:', error);
      mainContent.classList.remove('loading');
    });
}

function Maintenance_COE_Approval(page) {
  const mainContent = document.getElementById('main-content');

  // Start with content hidden and shifted
  mainContent.classList.add('loading');

  fetch(page)
    .then(response => response.text())
    .then(data => {
      mainContent.innerHTML = data;
      loadDepartments()

      Approvalflow_Search('add_approvallevel_search', 'employees_tbody')

      requestAnimationFrame(() => {
        mainContent.classList.remove('loading');
      });
    })
    .catch(error => {
      mainContent.innerHTML = '<p class="text-danger">Error loading page.</p>';
      console.error('Error:', error);
      mainContent.classList.remove('loading');
    });
}


document.getElementById('main-content').addEventListener('click', function (e) {
  if (e.target && e.target.id === 'coe-yes') {
    const page = e.target.getAttribute('data-page');
    fetch(page)
      .then(response => response.text())
      .then(data => {
        document.getElementById('main-content').innerHTML = data;

        //Generate Request ID
        const reqIdInput = document.getElementById('req_id');
        reqIdInput.value = generateCOEId();

        //Modal Summary
        ShowSummary('submit_btn');
        //Submit Request
        ConfirmRequest('submit_request');
        //Attach textarea auto-resize
        bindTextareaAutoResize('req_reason');


        // File Upload
        const fileInput = document.getElementById('files');
        const browseBtn = document.getElementById('btnBrowseFiles'); // Your browse button id

        if (fileInput && browseBtn) {
          browseBtn.addEventListener('click', () => fileInput.click());
          fileInput.addEventListener('change', handleFileSelect); // your file handler function
        }

      })
      .catch(error => {
        document.getElementById('main-content').innerHTML = '<p>Error loading content.</p>';
        console.error('Error:', error);
      });
  }
});

//DASHBOARD COE

function filterStatus(selectedStatus) {
  const cards = document.querySelectorAll('.status-card');
  const rows = document.querySelectorAll('#activityBody tr');

  // === Filter the cards ===
  cards.forEach(card => {
    const cardStatus = card.dataset.status;
    card.style.display = (selectedStatus === 'all' || cardStatus === selectedStatus) ? '' : 'none';
  });

  // === Filter the table rows ===
  rows.forEach(row => {
    const badge = row.querySelector('td:nth-child(3) .badge');
    if (!badge) return;
    const statusText = badge.textContent.trim().toLowerCase().replace(/\s/g, '');

    row.style.display = (selectedStatus === 'all' || selectedStatus === statusText) ? '' : 'none';
  });
}


// Simple client-side sorting for the table
function sortTable(colIndex) {
  const table = document.querySelector('table');
  const tbody = table.tBodies[0];
  const rows = Array.from(tbody.rows);
  let asc = table.getAttribute('data-sort-dir') !== 'asc';
  rows.sort((a, b) => {
    let cellA = a.cells[colIndex].innerText.toLowerCase();
    let cellB = b.cells[colIndex].innerText.toLowerCase();

    if (!isNaN(Date.parse(cellA)) && !isNaN(Date.parse(cellB))) {
      cellA = new Date(cellA);
      cellB = new Date(cellB);
    }

    if (cellA < cellB) return asc ? -1 : 1;
    if (cellA > cellB) return asc ? 1 : -1;
    return 0;
  });
  rows.forEach(row => tbody.appendChild(row));
  table.setAttribute('data-sort-dir', asc ? 'asc' : 'desc');
}

function refreshDashboardCounts() {
  fetch('dashboard/dashboard_coe_counts.php')
    .then(response => response.text())
    .then(data => {
      const parser = new DOMParser();
      const htmlDoc = parser.parseFromString(data, 'text/html');
      const spans = htmlDoc.querySelectorAll('.refresh-count');

      spans.forEach(span => {
        const status = span.getAttribute('data-status');
        const count = span.textContent;
        const element = document.getElementById('count-' + status);
        if (element) {
          element.textContent = count;
        }
      });
    })
    .catch(err => console.error('Error fetching counts:', err));
}

// Refresh every 1000ms (1 second)
setInterval(refreshDashboardCounts, 1000);

//CHAT DESK

function Load_ChatDesk() {
  fetch('chat_module/chat_main.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    }
  })
    .then(response => response.text())
    .then(data => {
      document.getElementById('main-content').innerHTML = data;

      initializeTabSwitching()
      fetchEmployees()
      fetchInbox()
      setupTabSwitching();
      setupChatFormSubmit()
      setupUserSearch();

      updateUserStatuses();

      inboxRefreshInterval = setInterval(fetchInbox, 5000); // Refresh every 5 seconds
    })
    .catch(error => {
      document.getElementById('main-content').innerHTML = '<p>Error loading request view.</p>';
      console.error('Fetch error:', error);
    });
}



//WTM SUBMENU

function wtm_mydtr_loadPage(page) {
  const mainContent = document.getElementById('main-content');
  mainContent.classList.add('loading');

  fetch(page)
    .then(response => response.text())
    .then(data => {
      mainContent.innerHTML = data;

      const employeeIdInput = document.querySelector('#employeeId');
      const employeeId = employeeIdInput.value;

      // Load Cutoff Dropdown
      loadCutoffDropdown();

      // Add listener to dropdown (after it's loaded into DOM)
      document.addEventListener('change', function (e) {
        if (e.target && e.target.id === 'cutoffDropdown') {
          cutoffChanged();
        }
      });

      function loadCutoffDropdown(dropdownId = 'cutoffDropdown', url = 'modules/fetch/wtm_cutoff_fetch.php') {
        const dropdown = document.getElementById(dropdownId);
        if (!dropdown) {
          console.error(`Dropdown element with ID "${dropdownId}" not found.`);
          return;
        }

        fetch(url)
          .then(response => response.text())
          .then(data => {
            dropdown.innerHTML = data;
          })
          .catch(error => {
            console.error('Error loading cutoff dropdown:', error);
          });
      }

      function cutoffChanged() {
        const dropdown = document.getElementById('cutoffDropdown');
        if (!dropdown || dropdown.selectedIndex === -1) return;

        const selectedOption = dropdown.options[dropdown.selectedIndex];
        const cutoff = selectedOption.value;
        const coFrom = selectedOption.getAttribute('data-co-from');
        const coTo = selectedOption.getAttribute('data-co-to');

        console.log('Selected cutoff:', cutoff);
        console.log('Cutoff date from:', coFrom);
        console.log('Cutoff date to:', coTo);

        loadAttendance(employeeId, coFrom, coTo);
      }

      function loadAttendance(employeeId, startDate, endDate) {
        const attendanceTableBody = document.querySelector('#attendanceTable tbody');
        if (!attendanceTableBody) return;

        attendanceTableBody.innerHTML = '<tr><td colspan="16" class="text-center">Loading Daily Time Record...</td></tr>';

        fetch(`modules/fetch/wtm_mydtr_fetch.php?employeeid=${employeeId}&start=${startDate}&end=${endDate}`, {
          cache: 'no-store'
        })
          .then(response => {
            if (!response.ok) throw new Error('Failed to fetch attendance');
            return response.json();
          })
          .then(data => {
            attendanceTableBody.innerHTML = '';

            if (!Array.isArray(data) || data.length === 0) {
              attendanceTableBody.innerHTML = '<tr><td colspan="16" class="text-center">No attendance data found.</td></tr>';
              return;
            }

            data.forEach(record => {
              const formattedDate = record.date ? new Date(record.date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '';
              attendanceTableBody.innerHTML += `
                <tr>
                  <td class="text-center small">${formattedDate}</td>
                  <td class="text-center small">${record.shiftcode || ''}</td>
                  <td class="text-center small">${record.dayofweek || ''}</td>

                  ${record.shiftin === '00:00:00' ? '<td class="text-center text-danger"></td>' : `<td class="text-center small">${record.shiftin?.slice(0, 5)}</td>`}
                  ${record.shiftout === '00:00:00' ? '<td class="text-center text-danger"></td>' : `<td class="text-center small">${record.shiftout?.slice(0, 5)}</td>`}

                  <td class="text-center small">${record.datein || ''}</td>
                  <td class="text-center small">${record.timein === '00:00:00' || record.shiftin === '00:00:00' ? '<span class="text-danger small">No Time In</span>' : record.timein?.slice(0, 5)}</td>
                  
                  <td class="text-center small">${record.dateout || ''}</td>
                  <td class="text-center small">${record.timeout === '00:00:00' || record.shiftin === '00:00:00' ? '<span class="text-danger small">No Time Out</span>' : record.timeout?.slice(0, 5)}</td>

                  ${record.tardiness === '0' ? '<td class="text-center text-success"></td>' : `<td class="text-center text-danger small">${record.tardiness || '0'}</td>`}
                  ${record.undertime === '0' ? '<td class="text-center text-success"></td>' : `<td class="text-center text-danger small">${record.undertime || '0'}</td>`}
                  ${record.totalmanhours === '0' || record.totalmanhours === '0.0' ? '<td class="text-center text-success"></td>' : `<td class="text-center text-danger small">${record.totalmanhours || '0'}</td>`}
                  ${record.nightdiff === '0' || record.nightdiff === '0.0' ? '<td class="text-center text-success"></td>' : `<td class="text-center text-danger small">${record.nightdiff || '0'}</td>`}
                  ${record.overtime === '0' ? '<td class="text-center text-success"></td>' : `<td class="text-center text-danger small">${record.overtime || '0'}</td>`}

                  <td class="text-center">
                    <button 
                      class="btn btn-sm btn-primary rounded-4"
                      data-bs-toggle="modal"
                      data-bs-target="#FormModal"
                      data-employeeid="${employeeId}"
                      data-date="${record.date}"
                      data-shiftcode="${record.shiftcode}"
                      data-shiftin="${record.shiftin}"
                      data-shiftout="${record.shiftout}"
                      data-datein="${record.datein}"
                      data-timein="${record.timein}"
                      data-dateout="${record.dateout}"
                      data-timeout="${record.timeout}"
                      data-tardiness="${record.tardiness}"
                      data-undertime="${record.undertime}"
                      data-totalmanhours="${record.totalmanhours}"
                      data-nightdiff="${record.nightdiff}"
                      data-overtime="${record.overtime}"
                      data-overtime="${record.overtime}"
                      data-remarks="${record.remarks}">
                      <ion-icon name="document-text-outline"></ion-icon>
                    </button>
                  </td>

                  <td class="text-center">
                    ${record.remarks
                  ? `<i class="bi bi-info-circle-fill text-primary" data-bs-toggle="tooltip" title="${record.remarks}"></i>`
                  : ''}
                  </td>
                </tr>
              `;
            });

            // Re-enable tooltips
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            [...tooltipTriggerList].forEach(el => new bootstrap.Tooltip(el));
          })
          .catch(error => {
            attendanceTableBody.innerHTML = '<tr><td colspan="16" class="text-center text-danger">Failed to load attendance.</td></tr>';
            console.error(error);
          });
      }

      function initializeFormModal(modalId = 'FormModal') {
        const formModal = document.getElementById(modalId);
        if (!formModal) {
          console.error(`Modal with ID "${modalId}" not found.`);
          return;
        }

        formModal.addEventListener('show.bs.modal', function (event) {
          const button = event.relatedTarget;
          if (!button) return;

          function formatDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return isNaN(date.getTime()) ? '' : date.toLocaleDateString('en-US', {
              month: 'short',
              day: '2-digit',
              year: 'numeric'
            });
          }

          const formatTime = (timeStr) => {
            if (!timeStr || timeStr === '00:00:00') return '';
            return timeStr.slice(0, 5);
          };

          const date = formatDate(button.getAttribute('data-date'));
          const shiftcode = button.getAttribute('data-shiftcode') || '';
          const shiftin = button.getAttribute('data-shiftin') || '';
          const shiftout = button.getAttribute('data-shiftout') || '';

          const datein = formatDate(button.getAttribute('data-datein'));
          const timein = button.getAttribute('data-timein') || '';
          const dateout = formatDate(button.getAttribute('data-dateout'));
          const timeout = button.getAttribute('data-timeout') || '';

          const tardiness = button.getAttribute('data-tardiness') || '';
          const undertime = button.getAttribute('data-undertime') || '';
          const nightdiff = button.getAttribute('data-nightdiff') || '';
          const overtime = button.getAttribute('data-overtime') || '';
          const totalmanhours = button.getAttribute('data-totalmanhours') || '';
          const remarks = button.getAttribute('data-remarks') || '';

          // Date (bold value)
          formModal.querySelector('#modal-header-date').innerHTML = `DTR Data: <strong>${date}</strong>`;
          formModal.querySelector('#modal-date').innerHTML = `Date: <strong>${date}</strong>`;

          // Shift with Change Shift button
          let shiftDisplay = '';
          if (shiftin === '00:00:00' && shiftout === '00:00:00') {
            shiftDisplay = `<strong>[${shiftcode}]</strong>`;
          } else {
            shiftDisplay = `<strong>[${shiftcode}] ${formatTime(shiftin)} - ${formatTime(shiftout)}</strong>`;
          }
          // Add Change Shift button
          shiftDisplay += ` <button type="button" class="btn btn-sm btn-outline-primary rounded-4 ms-2" id="btnChangeShift">Change Shift</button>`;
          formModal.querySelector('#modal-shift').innerHTML = `Shift: ${shiftDisplay}`;

          // Attendance Time In with Request Manual In button
          let AttendanceTimeInDisplay = '';
          if (timein === '00:00:00') {
            AttendanceTimeInDisplay = '<strong>No Time In</strong>';
          } else {
            AttendanceTimeInDisplay = `<strong>${formatTime(timein)}</strong>`;
          }
          let attendanceInHTML = (datein || timein)
            ? `Date & Time In: <strong>${datein}</strong> ${AttendanceTimeInDisplay}`
            : '';
          if (attendanceInHTML) {
            attendanceInHTML += ` <button type="button" class="btn btn-sm btn-outline-secondary rounded-4 ms-2" id="btnRequestManualIn">Request Manual In</button>`;
          }
          formModal.querySelector('#modal-in').innerHTML = attendanceInHTML;

          // Attendance Time Out with Request Manual Out button
          let AttendanceTimeOutDisplay = '';
          if (timeout === '00:00:00') {
            AttendanceTimeOutDisplay = '<strong>No Time Out</strong>';
          } else {
            AttendanceTimeOutDisplay = `<strong>${formatTime(timeout)}</strong>`;
          }
          let attendanceOutHTML = (dateout || timeout)
            ? `Date & Time Out: <strong>${dateout}</strong> ${AttendanceTimeOutDisplay}`
            : '';
          if (attendanceOutHTML) {
            attendanceOutHTML += ` <button type="button" class="btn btn-sm btn-outline-secondary rounded-4 ms-2" id="btnRequestManualOut">Request Manual Out</button>`;
          }
          formModal.querySelector('#modal-out').innerHTML = attendanceOutHTML;

          // Tardiness
          formModal.querySelector('#modal-tardiness').innerHTML = `Tardiness: <strong>${tardiness} minute(s)</strong>`;

          // Undertime
          formModal.querySelector('#modal-undertime').innerHTML = `Undertime: <strong>${undertime} minute(s)</strong>`;

          // NightDiff
          formModal.querySelector('#modal-nightdiff').innerHTML = `NightDiff: <strong>${nightdiff} hour(s)</strong>`;

          // Overtime with File Overtime button
          let overtimeHTML = `Excess: <strong>${overtime} hour(s)</strong>`;
          overtimeHTML += ` <button type="button" class="btn btn-sm btn-outline-success rounded-4 ms-2" id="btnFileOvertime" data-bs-dismiss="modal" onclick="wtm_fileot_loadPage('modules/wtm_file_overtime.php')">File Overtime</button>`;
          overtimeHTML += ` <button type="button" class="btn btn-sm btn-outline-success rounded-4 ms-2" id="btnFileOvertime">File Opentime Overtime</button>`;
          formModal.querySelector('#modal-overtime').innerHTML = overtimeHTML;

          // // Man Hours
          // formModal.querySelector('#modal-totalmanhours').innerHTML = `Man Hours: <strong>${totalmanhours}</strong>`;

          // Remarks
          formModal.querySelector('#modal-remarks').innerHTML = `<strong>${remarks}</strong>`;

        });
      }

      initializeFormModal();

      // Animate slide-in
      requestAnimationFrame(() => {
        mainContent.classList.remove('loading');
      });
    })
    .catch(error => {
      mainContent.innerHTML = '<p class="text-danger">Error loading page.</p>';
      console.error('Error:', error);
      mainContent.classList.remove('loading');
    });
}



//WTM SUBMENU

function wtm_fileot_loadPage(page) {
  const mainContent = document.getElementById('main-content');
  mainContent.classList.add('loading');

  fetch(page)
    .then(response => response.text())
    .then(data => {
      mainContent.innerHTML = data;

      console.log('File Overtime Page Loaded');

      loadAttendance('PDMC000325', '2025-06-05', '2025-06-20');

      function loadAttendance(employeeId, startDate, endDate) {
        const attendanceTableBody = document.querySelector('#attendanceTable tbody');
        if (!attendanceTableBody) return;

        attendanceTableBody.innerHTML = '<tr><td colspan="16" class="text-center">Loading Daily Time Record...</td></tr>';

        fetch(`modules/fetch/wtm_mydtr_fetch.php?employeeid=${employeeId}&start=${startDate}&end=${endDate}`, {
          cache: 'no-store'
        })
          .then(response => {
            if (!response.ok) throw new Error('Failed to fetch attendance');
            return response.json();
          })
          .then(data => {
            attendanceTableBody.innerHTML = '';

            if (!Array.isArray(data) || data.length === 0) {
              attendanceTableBody.innerHTML = '<tr><td colspan="16" class="text-center">No attendance data found.</td></tr>';
              return;
            }

            data.forEach(record => {
              const formattedDate = record.date ? new Date(record.date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '';
              attendanceTableBody.innerHTML += `
                <tr>
                  <td class="text-center small">${formattedDate}</td>
                  <td class="text-center small">${record.shiftcode || ''}</td>
                  <td class="text-center small">${record.dayofweek || ''}</td>

                  ${record.shiftin === '00:00:00' ? '<td class="text-center text-danger"></td>' : `<td class="text-center small">${record.shiftin?.slice(0, 5)}</td>`}
                  ${record.shiftout === '00:00:00' ? '<td class="text-center text-danger"></td>' : `<td class="text-center small">${record.shiftout?.slice(0, 5)}</td>`}

                  <td class="text-center small">${record.datein || ''}</td>
                  <td class="text-center small">${record.timein === '00:00:00' || record.shiftin === '00:00:00' ? '<span class="text-danger small">No Time In</span>' : record.timein?.slice(0, 5)}</td>
                  
                  <td class="text-center small">${record.dateout || ''}</td>
                  <td class="text-center small">${record.timeout === '00:00:00' || record.shiftin === '00:00:00' ? '<span class="text-danger small">No Time Out</span>' : record.timeout?.slice(0, 5)}</td>

                  ${record.tardiness === '0' ? '<td class="text-center text-success"></td>' : `<td class="text-center text-danger small">${record.tardiness || '0'}</td>`}
                  ${record.undertime === '0' ? '<td class="text-center text-success"></td>' : `<td class="text-center text-danger small">${record.undertime || '0'}</td>`}
                  ${record.totalmanhours === '0' || record.totalmanhours === '0.0' ? '<td class="text-center text-success"></td>' : `<td class="text-center text-danger small">${record.totalmanhours || '0'}</td>`}
                  ${record.nightdiff === '0' || record.nightdiff === '0.0' ? '<td class="text-center text-success"></td>' : `<td class="text-center text-danger small">${record.nightdiff || '0'}</td>`}
                  ${record.overtime === '0' ? '<td class="text-center text-success"></td>' : `<td class="text-center text-danger small">${record.overtime || '0'}</td>`}

                  <td class="text-center">
                    <button 
                      class="btn btn-sm btn-primary rounded-4"
                      data-bs-toggle="modal"
                      data-bs-target="#FormModal"
                      data-employeeid="${employeeId}"
                      data-date="${record.date}"
                      data-shiftcode="${record.shiftcode}"
                      data-shiftin="${record.shiftin}"
                      data-shiftout="${record.shiftout}"
                      data-datein="${record.datein}"
                      data-timein="${record.timein}"
                      data-dateout="${record.dateout}"
                      data-timeout="${record.timeout}"
                      data-tardiness="${record.tardiness}"
                      data-undertime="${record.undertime}"
                      data-totalmanhours="${record.totalmanhours}"
                      data-nightdiff="${record.nightdiff}"
                      data-overtime="${record.overtime}"
                      data-overtime="${record.overtime}"
                      data-remarks="${record.remarks}">
                      <ion-icon name="document-text-outline"></ion-icon>
                    </button>
                  </td>

                  <td class="text-center">
                    ${record.remarks
                  ? `<i class="bi bi-info-circle-fill text-primary" data-bs-toggle="tooltip" title="${record.remarks}"></i>`
                  : ''}
                  </td>
                </tr>
              `;
            });

            // Re-enable tooltips
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            [...tooltipTriggerList].forEach(el => new bootstrap.Tooltip(el));
          })
          .catch(error => {
            attendanceTableBody.innerHTML = '<tr><td colspan="16" class="text-center text-danger">Failed to load attendance.</td></tr>';
            console.error(error);
          });
      }

      // Animate slide-in
      requestAnimationFrame(() => {
        mainContent.classList.remove('loading');
      });
    })
    .catch(error => {
      mainContent.innerHTML = '<p class="text-danger">Error loading page.</p>';
      console.error('Error:', error);
      mainContent.classList.remove('loading');
    });
}


//WTM SUBMENU

function wtm_backtoDTR_loadPage(page) {
  const mainContent = document.getElementById('main-content');
  mainContent.classList.add('loading');

  fetch(page)
    .then(response => response.text())
    .then(data => {
      mainContent.innerHTML = data;

      console.log('File Overtime Page Loaded');

      loadAttendance('PDMC000325', '2025-06-05', '2025-06-20');

      function loadAttendance(employeeId, startDate, endDate) {
        const attendanceTableBody = document.querySelector('#attendanceTable tbody');
        if (!attendanceTableBody) return;

        attendanceTableBody.innerHTML = '<tr><td colspan="16" class="text-center">Loading Daily Time Record...</td></tr>';

        fetch(`modules/fetch/wtm_mydtr_fetch.php?employeeid=${employeeId}&start=${startDate}&end=${endDate}`, {
          cache: 'no-store'
        })
          .then(response => {
            if (!response.ok) throw new Error('Failed to fetch attendance');
            return response.json();
          })
          .then(data => {
            attendanceTableBody.innerHTML = '';

            if (!Array.isArray(data) || data.length === 0) {
              attendanceTableBody.innerHTML = '<tr><td colspan="16" class="text-center">No attendance data found.</td></tr>';
              return;
            }

            data.forEach(record => {
              const formattedDate = record.date ? new Date(record.date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '';
              attendanceTableBody.innerHTML += `
                <tr>
                  <td class="text-center small">${formattedDate}</td>
                  <td class="text-center small">${record.shiftcode || ''}</td>
                  <td class="text-center small">${record.dayofweek || ''}</td>

                  ${record.shiftin === '00:00:00' ? '<td class="text-center text-danger"></td>' : `<td class="text-center small">${record.shiftin?.slice(0, 5)}</td>`}
                  ${record.shiftout === '00:00:00' ? '<td class="text-center text-danger"></td>' : `<td class="text-center small">${record.shiftout?.slice(0, 5)}</td>`}

                  <td class="text-center small">${record.datein || ''}</td>
                  <td class="text-center small">${record.timein === '00:00:00' || record.shiftin === '00:00:00' ? '<span class="text-danger small">No Time In</span>' : record.timein?.slice(0, 5)}</td>
                  
                  <td class="text-center small">${record.dateout || ''}</td>
                  <td class="text-center small">${record.timeout === '00:00:00' || record.shiftin === '00:00:00' ? '<span class="text-danger small">No Time Out</span>' : record.timeout?.slice(0, 5)}</td>

                  ${record.tardiness === '0' ? '<td class="text-center text-success"></td>' : `<td class="text-center text-danger small">${record.tardiness || '0'}</td>`}
                  ${record.undertime === '0' ? '<td class="text-center text-success"></td>' : `<td class="text-center text-danger small">${record.undertime || '0'}</td>`}
                  ${record.totalmanhours === '0' || record.totalmanhours === '0.0' ? '<td class="text-center text-success"></td>' : `<td class="text-center text-danger small">${record.totalmanhours || '0'}</td>`}
                  ${record.nightdiff === '0' || record.nightdiff === '0.0' ? '<td class="text-center text-success"></td>' : `<td class="text-center text-danger small">${record.nightdiff || '0'}</td>`}
                  ${record.overtime === '0' ? '<td class="text-center text-success"></td>' : `<td class="text-center text-danger small">${record.overtime || '0'}</td>`}

                  <td class="text-center">
                    <button 
                      class="btn btn-sm btn-primary rounded-4"
                      data-bs-toggle="modal"
                      data-bs-target="#FormModal"
                      data-employeeid="${employeeId}"
                      data-date="${record.date}"
                      data-shiftcode="${record.shiftcode}"
                      data-shiftin="${record.shiftin}"
                      data-shiftout="${record.shiftout}"
                      data-datein="${record.datein}"
                      data-timein="${record.timein}"
                      data-dateout="${record.dateout}"
                      data-timeout="${record.timeout}"
                      data-tardiness="${record.tardiness}"
                      data-undertime="${record.undertime}"
                      data-totalmanhours="${record.totalmanhours}"
                      data-nightdiff="${record.nightdiff}"
                      data-overtime="${record.overtime}"
                      data-overtime="${record.overtime}"
                      data-remarks="${record.remarks}">
                      <ion-icon name="document-text-outline"></ion-icon>
                    </button>
                  </td>

                  <td class="text-center">
                    ${record.remarks
                  ? `<i class="bi bi-info-circle-fill text-primary" data-bs-toggle="tooltip" title="${record.remarks}"></i>`
                  : ''}
                  </td>
                </tr>
              `;
            });

            function initializeFormModal(modalId = 'FormModal') {
              const formModal = document.getElementById(modalId);
              if (!formModal) {
                console.error(`Modal with ID "${modalId}" not found.`);
                return;
              }

              formModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (!button) return;

                function formatDate(dateStr) {
                  if (!dateStr) return '';
                  const date = new Date(dateStr);
                  return isNaN(date.getTime()) ? '' : date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: '2-digit',
                    year: 'numeric'
                  });
                }

                const formatTime = (timeStr) => {
                  if (!timeStr || timeStr === '00:00:00') return '';
                  return timeStr.slice(0, 5);
                };

                const date = formatDate(button.getAttribute('data-date'));
                const shiftcode = button.getAttribute('data-shiftcode') || '';
                const shiftin = button.getAttribute('data-shiftin') || '';
                const shiftout = button.getAttribute('data-shiftout') || '';

                const datein = formatDate(button.getAttribute('data-datein'));
                const timein = button.getAttribute('data-timein') || '';
                const dateout = formatDate(button.getAttribute('data-dateout'));
                const timeout = button.getAttribute('data-timeout') || '';

                const tardiness = button.getAttribute('data-tardiness') || '';
                const undertime = button.getAttribute('data-undertime') || '';
                const nightdiff = button.getAttribute('data-nightdiff') || '';
                const overtime = button.getAttribute('data-overtime') || '';
                const totalmanhours = button.getAttribute('data-totalmanhours') || '';
                const remarks = button.getAttribute('data-remarks') || '';

                // Date (bold value)
                formModal.querySelector('#modal-header-date').innerHTML = `DTR Data: <strong>${date}</strong>`;
                formModal.querySelector('#modal-date').innerHTML = `Date: <strong>${date}</strong>`;

                // Shift with Change Shift button
                let shiftDisplay = '';
                if (shiftin === '00:00:00' && shiftout === '00:00:00') {
                  shiftDisplay = `<strong>[${shiftcode}]</strong>`;
                } else {
                  shiftDisplay = `<strong>[${shiftcode}] ${formatTime(shiftin)} - ${formatTime(shiftout)}</strong>`;
                }
                // Add Change Shift button
                shiftDisplay += ` <button type="button" class="btn btn-sm btn-outline-primary rounded-4 ms-2" id="btnChangeShift">Change Shift</button>`;
                formModal.querySelector('#modal-shift').innerHTML = `Shift: ${shiftDisplay}`;

                // Attendance Time In with Request Manual In button
                let AttendanceTimeInDisplay = '';
                if (timein === '00:00:00') {
                  AttendanceTimeInDisplay = '<strong>No Time In</strong>';
                } else {
                  AttendanceTimeInDisplay = `<strong>${formatTime(timein)}</strong>`;
                }
                let attendanceInHTML = (datein || timein)
                  ? `Date & Time In: <strong>${datein}</strong> ${AttendanceTimeInDisplay}`
                  : '';
                if (attendanceInHTML) {
                  attendanceInHTML += ` <button type="button" class="btn btn-sm btn-outline-secondary rounded-4 ms-2" id="btnRequestManualIn">Request Manual In</button>`;
                }
                formModal.querySelector('#modal-in').innerHTML = attendanceInHTML;

                // Attendance Time Out with Request Manual Out button
                let AttendanceTimeOutDisplay = '';
                if (timeout === '00:00:00') {
                  AttendanceTimeOutDisplay = '<strong>No Time Out</strong>';
                } else {
                  AttendanceTimeOutDisplay = `<strong>${formatTime(timeout)}</strong>`;
                }
                let attendanceOutHTML = (dateout || timeout)
                  ? `Date & Time Out: <strong>${dateout}</strong> ${AttendanceTimeOutDisplay}`
                  : '';
                if (attendanceOutHTML) {
                  attendanceOutHTML += ` <button type="button" class="btn btn-sm btn-outline-secondary rounded-4 ms-2" id="btnRequestManualOut">Request Manual Out</button>`;
                }
                formModal.querySelector('#modal-out').innerHTML = attendanceOutHTML;

                // Tardiness
                formModal.querySelector('#modal-tardiness').innerHTML = `Tardiness: <strong>${tardiness} minute(s)</strong>`;

                // Undertime
                formModal.querySelector('#modal-undertime').innerHTML = `Undertime: <strong>${undertime} minute(s)</strong>`;

                // NightDiff
                formModal.querySelector('#modal-nightdiff').innerHTML = `NightDiff: <strong>${nightdiff} hour(s)</strong>`;

                // Overtime with File Overtime button
                let overtimeHTML = `Excess: <strong>${overtime} hour(s)</strong>`;
                overtimeHTML += ` <button type="button" class="btn btn-sm btn-outline-success rounded-4 ms-2" id="btnFileOvertime" data-bs-dismiss="modal" onclick="wtm_fileot_loadPage('modules/wtm_file_overtime.php')">File Overtime</button>`;
                overtimeHTML += ` <button type="button" class="btn btn-sm btn-outline-success rounded-4 ms-2" id="btnFileOvertime">File Opentime Overtime</button>`;
                formModal.querySelector('#modal-overtime').innerHTML = overtimeHTML;

                // // Man Hours
                // formModal.querySelector('#modal-totalmanhours').innerHTML = `Man Hours: <strong>${totalmanhours}</strong>`;

                // Remarks
                formModal.querySelector('#modal-remarks').innerHTML = `<strong>${remarks}</strong>`;

              });
            }

            initializeFormModal();

            // Re-enable tooltips
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            [...tooltipTriggerList].forEach(el => new bootstrap.Tooltip(el));
          })
          .catch(error => {
            attendanceTableBody.innerHTML = '<tr><td colspan="16" class="text-center text-danger">Failed to load attendance.</td></tr>';
            console.error(error);
          });
      }

      // Animate slide-in
      requestAnimationFrame(() => {
        mainContent.classList.remove('loading');
      });
    })
    .catch(error => {
      mainContent.innerHTML = '<p class="text-danger">Error loading page.</p>';
      console.error('Error:', error);
      mainContent.classList.remove('loading');
    });
}

