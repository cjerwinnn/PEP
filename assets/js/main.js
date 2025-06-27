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
  fetch('dashboard_wtms.php')
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

      UpdateOnlineStatus = setInterval(updateUserStatuses, 0); // Refresh every 5 seconds

    })
    .catch(error => {
      document.getElementById('main-content').innerHTML = '<p>Error loading request view.</p>';
      console.error('Fetch error:', error);
    });
}
