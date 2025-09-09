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
  loadBRTable();
}


function ViewAttendance(employeeId, requestId) {
  if (!employeeId || !requestId) return;

  $.ajax({
    url: '../fetch/wtm/wtm_attendancevalidation_details.php',
    type: 'POST',
    data: { employee_id: employeeId, request_id: requestId },
    success: function (response) {
      console.log('AJAX Response:', response);
      window.location.href = 'wtm_manualattendance_validation_view.php';
    },
    error: function (xhr, status, error) {
      console.error("AJAX Error:", status, error);
      console.log('Response Text:', xhr.responseText);
    }
  });
}

document.addEventListener('DOMContentLoaded', function () {

  // === Default settings ===
  let rowsPerPage = 10;
  let currentPage = 1;
  const maxPaginationButtons = 10;

  const table = document.getElementById('wtm_shiftschedule_employeelist');
  const pagination = document.getElementById('pagination');
  const startCount = document.getElementById('startCount');
  const endCount = document.getElementById('endCount');
  const totalEntries = document.getElementById('totalEntries');
  const tableBody = table.querySelector('tbody');

  let allRows = Array.from(tableBody.querySelectorAll('tr'));
  let filteredRows = allRows;

  // === Sorting ===
  let sortedColumnIndex = null;
  let ascending = true;

  function sortRows(rows, index, asc) {
    if (!rows.length) return rows;

    const sampleCell = rows[0].children[index].innerText.trim();
    const isNumeric = !isNaN(sampleCell.replace(/,/g, ""));
    const isDate = !isNumeric && !isNaN(Date.parse(sampleCell));

    return rows.slice().sort((a, b) => {
      const aText = a.children[index].innerText.trim();
      const bText = b.children[index].innerText.trim();

      if (isNumeric) return asc ? parseFloat(aText.replace(/,/g, "")) - parseFloat(bText.replace(/,/g, ""))
        : parseFloat(bText.replace(/,/g, "")) - parseFloat(aText.replace(/,/g, ""));
      if (isDate) return asc ? new Date(aText) - new Date(bText) : new Date(bText) - new Date(aText);
      return asc ? aText.localeCompare(bText) : bText.localeCompare(aText);
    });
  }

  document.querySelectorAll("#dataTable th.sortable").forEach((header, index) => {
    header.addEventListener('click', () => {
      if (sortedColumnIndex === index) ascending = !ascending;
      else { sortedColumnIndex = index; ascending = true; }

      // Remove icons from all headers
      document.querySelectorAll("#dataTable th.sortable .sort-icon").forEach(icon => icon.textContent = '');
      const icon = header.querySelector('.sort-icon');
      if (icon) icon.textContent = ascending ? " ▲" : " ▼";

      // Sort master rows array
      allRows = sortRows(allRows, sortedColumnIndex, ascending);

      // Reapply search & pagination
      searchTable(false); // keep currentPage
    });
  });

  // === Rows per page dropdown ===
  const rowsSelect = document.getElementById('rowsPerPage');
  if (rowsSelect) {
    rowsSelect.value = rowsPerPage;
    rowsSelect.addEventListener('change', function () {
      rowsPerPage = parseInt(this.value);
      currentPage = 1;
      showPage(currentPage);
    });
  }

  // === Search input ===
  const searchInput = document.getElementById('searchCEBar');
  if (searchInput) {
    searchInput.value = '';
    searchInput.addEventListener('input', () => searchTable(true)); // reset page on search
  }

  // === Status checkboxes ===
  const allStatusCheckbox = document.querySelector('.status-checkbox[value=""]'); // All Status
  const statusCheckboxes = document.querySelectorAll('.status-checkbox');

  // Pre-check default "WAITING FOR L1 APPROVAL"
  statusCheckboxes.forEach(cb => {
    cb.checked = cb.value.trim() === "PENDING";
  });

  function handleStatusCheckboxes(changedCb) {
    if (changedCb === allStatusCheckbox && allStatusCheckbox.checked) {
      statusCheckboxes.forEach(cb => { if (cb !== allStatusCheckbox) cb.checked = false; });
    } else {
      const anyChecked = Array.from(statusCheckboxes)
        .some(cb => cb !== allStatusCheckbox && cb.checked);
      allStatusCheckbox.checked = !anyChecked;
    }
  }

  // Attach event listener to all checkboxes
  statusCheckboxes.forEach(cb => {
    cb.addEventListener('change', () => {
      handleStatusCheckboxes(cb);
      loadBRTable(); // refresh table when status changes
    });
  });

  totalEntries.textContent = allRows.length;

  // === Pagination and display functions ===
  function showPage(page) {
    if (page < 1) page = 1;
    const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
    if (page > totalPages) page = totalPages;

    const startRow = (page - 1) * rowsPerPage;
    const endRow = startRow + rowsPerPage;

    tableBody.innerHTML = '';

    if (filteredRows.length > 0) {
      filteredRows.slice(startRow, endRow).forEach(row => tableBody.appendChild(row));

      startCount.textContent = startRow + 1;
      endCount.textContent = Math.min(endRow, filteredRows.length);

      if (totalPages > 1) renderPaginationButtons(page);
      else pagination.innerHTML = '';
    } else {
      const noResultsRow = document.createElement('tr');
      const noResultsCell = document.createElement('td');
      const colCount = allRows.length > 0 ? allRows[0].cells.length : table.rows[0].cells.length;
      noResultsCell.textContent = 'No results found';
      noResultsCell.className = 'text-center';
      noResultsCell.colSpan = colCount;
      noResultsRow.appendChild(noResultsCell);
      tableBody.appendChild(noResultsRow);

      startCount.textContent = 0;
      endCount.textContent = 0;
      pagination.innerHTML = '';
    }
  }

  function renderPaginationButtons(page) {
    pagination.innerHTML = '';
    const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
    if (totalPages <= 1) return;

    if (page > 1) {
      pagination.appendChild(createPaginationButton('FIRST', 1));
      pagination.appendChild(createPaginationButton('PREVIOUS', page - 1));
    }

    let startPage = Math.max(1, page - Math.floor(maxPaginationButtons / 2));
    let endPage = Math.min(totalPages, startPage + maxPaginationButtons - 1);
    if (endPage - startPage < maxPaginationButtons - 1) {
      startPage = Math.max(1, endPage - maxPaginationButtons + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
      pagination.appendChild(createPaginationButton(i, i, i === page));
    }

    if (page < totalPages) {
      pagination.appendChild(createPaginationButton('NEXT', page + 1));
      pagination.appendChild(createPaginationButton('LAST', totalPages));
    }
  }

  function createPaginationButton(text, page, isActive = false) {
    const li = document.createElement('li');
    li.className = `page-item${isActive ? ' active' : ''}`;
    li.innerHTML = `<a class="page-link" href="#">${text}</a>`;
    li.addEventListener('click', e => {
      e.preventDefault();
      currentPage = page;
      showPage(currentPage);
    });
    return li;
  }

  function searchTable(resetPage = false) {
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';

    let tempRows = allRows.slice();

    if (sortedColumnIndex !== null) {
      tempRows = sortRows(tempRows, sortedColumnIndex, ascending);
    }

    filteredRows = tempRows.filter(row => {
      if (!searchTerm) return true;
      return Array.from(row.cells).some(cell =>
        cell.innerText.toLowerCase().includes(searchTerm)
      );
    });

    totalEntries.textContent = filteredRows.length;

    if (resetPage) currentPage = 1;
    showPage(currentPage);
  }

  // === Load table from server ===
  window.loadBRTable = function () {
    const checkedStatuses = Array.from(document.querySelectorAll('.status-checkbox:checked'))
      .map(cb => cb.value.trim())
      .join(',');

    fetch('../fetch/wtm/wtm_attendancevalidation_list.php?statuses=' + encodeURIComponent(checkedStatuses) +
      '&month=' + encodeURIComponent(document.getElementById('monthDropdown').value) +
      '&year=' + encodeURIComponent(document.getElementById('yearDropdown').value))
      .then(res => res.text())
      .then(data => {
        tableBody.innerHTML = data;
        allRows = Array.from(tableBody.querySelectorAll('tr'));
        searchTable(false); // keep currentPage on refresh
      })
      .catch(err => console.error(err));
  };

  // Auto-refresh every 2 sec
  setInterval(loadBRTable, 2000);

  // Initial display
  searchTable();

  loadBRTable();

});
