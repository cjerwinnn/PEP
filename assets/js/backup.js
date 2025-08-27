//CALENDAR ONLY CODE 

function selectDate(event) {
  const td = event.currentTarget;

  // Skip disabled days
  if (td.classList.contains('disabled-day')) {
    return;
  }

  const date = td.dataset.date;
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
      if (cell.classList.contains('disabled-day')) continue; // skip disabled
      const d = cell.dataset.date;
      if (!selectedDates.includes(d)) selectedDates.push(d);
      cell.classList.add('selected');
    }
  }

  lastClickedDate = date;
  document.getElementById('selectedDates').textContent = selectedDates.join(', ') || 'None';
}
