function escapeHtml(text) {
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

let currentPage = 1;
let pageLimit = 10;

function searchCETable() {
    currentPage = 1;
    applyFiltersAndPaginate();
}

function paginateTable() {
    applyFiltersAndPaginate();
}

function applyFiltersAndPaginate() {
    const input = document.getElementById("ceSearchBar");
    const filter = input.value.toLowerCase();
    const table = document.getElementById("maintenance_checklist_table");
    const tbody = table.querySelector("tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));
    const pageLimitSelect = document.getElementById("maintenance_checklist_tableLimit");
    pageLimit = parseInt(pageLimitSelect.value);

    const filteredRows = rows.filter(row => {
        const cells = row.getElementsByTagName("td");
        for (let i = 0; i < cells.length; i++) {
            const cellText = cells[i].textContent || cells[i].innerText;
            if (cellText.toLowerCase().includes(filter)) return true;
        }
        return false;
    });

    const totalRows = filteredRows.length;
    const totalPages = Math.max(1, Math.ceil(totalRows / pageLimit));

    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    rows.forEach(row => row.style.display = "none");
    const start = (currentPage - 1) * pageLimit;
    const end = start + pageLimit;
    filteredRows.slice(start, end).forEach(row => row.style.display = "");

    document.getElementById("cePageInfo").textContent = `Page ${currentPage} of ${totalPages}`;
}

function nextPage() {
    currentPage++;
    applyFiltersAndPaginate();
}

function prevPage() {
    currentPage--;
    applyFiltersAndPaginate();
}

function firstPage() {
    currentPage = 1;
    applyFiltersAndPaginate();
}

function lastPage() {
    const input = document.getElementById("ceSearchBar");
    const filter = input.value.toLowerCase();
    const table = document.getElementById("ceTable");
    const rows = Array.from(table.querySelector("tbody").querySelectorAll("tr"));
    const filteredRows = rows.filter(row => {
        const cells = row.getElementsByTagName("td");
        for (let i = 0; i < cells.length; i++) {
            const cellText = cells[i].textContent || cells[i].innerText;
            if (cellText.toLowerCase().includes(filter)) return true;
        }
        return false;
    });

    const totalPages = Math.max(1, Math.ceil(filteredRows.length / pageLimit));
    currentPage = totalPages;
    applyFiltersAndPaginate();
}

function maintenance_coechecklist_filter() {
    const input = document.getElementById('ceSearchBar').value.toLowerCase();
    const filter = document.getElementById('ceFilterDropdown').value.toLowerCase();
    const table = document.getElementById('maintenance_checklist_table');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    let visibleCount = 0;
    const limit = parseInt(document.getElementById('maintenance_checklist_tableLimit').value);
    const pageInfo = document.getElementById('cePageInfo');

    for (let i = 0; i < rows.length; i++) {
        const cols = rows[i].getElementsByTagName('td');
        const rowText = rows[i].innerText.toLowerCase();
        const coeType = cols[0].innerText.toLowerCase();

        const matchesSearch = rowText.includes(input);
        const matchesFilter = filter === "" || coeType.includes(filter);

        if (matchesSearch && matchesFilter) {
            rows[i].style.display = visibleCount < limit ? "" : "none";
            visibleCount++;
        } else {
            rows[i].style.display = "none";
        }
    }

    pageInfo.innerText = `Showing ${Math.min(visibleCount, limit)} of ${visibleCount} entries`;
}

