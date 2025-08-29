function computeTardiness(shiftDate, shiftStart, shiftEnd, timeInDate, timeIn) {

    if (!shiftStart || !shiftEnd || shiftStart === '00:00:00' || shiftEnd === '00:00:00') return 0;

    // If employee didn't clock in
    if (!timeIn || timeIn === '00:00:00') return 0;

    const shiftDateTime = new Date(`${shiftDate}T${shiftStart}`);
    const timeInDateTime = new Date(`${timeInDate}T${timeIn}`);

    const diffMs = timeInDateTime - shiftDateTime;

    // If early or on time
    if (diffMs <= 0) return 0;

    const diffMin = Math.floor(diffMs / 60000); // milliseconds to minutes

    return diffMin;
}

function computeUndertime(shiftDate, shiftStart, shiftEnd, timeOutDate, timeOut) {

    if (!shiftStart || !shiftEnd || shiftStart === '00:00:00' || shiftEnd === '00:00:00') return 0;

    if (!shiftDate || !shiftStart || !shiftEnd || !timeOutDate || !timeOut || timeOut === '00:00:00') return 0;

    // Ensure valid datetime strings
    const shiftEndStr = `${shiftDate}T${shiftEnd}`;
    const timeOutStr = `${timeOutDate}T${timeOut}`;

    const shiftEndDateTime = new Date(shiftEndStr);
    const timeOutDateTime = new Date(timeOutStr);

    // Check for invalid date objects
    if (isNaN(shiftEndDateTime.getTime()) || isNaN(timeOutDateTime.getTime())) {
        console.warn(`Invalid date input: shiftEnd="${shiftEndStr}", timeOut="${timeOutStr}"`);
        return 0;
    }

    const diffMs = shiftEndDateTime - timeOutDateTime;

    if (diffMs <= 0) return 0;

    const diffMin = Math.floor(diffMs / 60000); // Convert ms to minutes
    return diffMin;
}


function computeOvertime(shiftDate, shiftStart, shiftOut, dateOut, timeOut) {

    if (!shiftStart || !shiftOut || shiftStart === '00:00:00' || shiftOut === '00:00:00') return 0;
    if (!timeOut || timeOut === '00:00:00') return 0;

    const shiftOutHour = parseInt(shiftOut.split(':')[0], 10);
    let adjustedShiftDate = shiftDate;

    // If shift out is after midnight (before 12 PM), assume next day
    if (shiftOutHour < 12) {
        const shiftDateObj = new Date(shiftDate);
        shiftDateObj.setDate(shiftDateObj.getDate() + 1);
        adjustedShiftDate = shiftDateObj.toISOString().split('T')[0];
    }

    const shiftOutDateTime = new Date(`${adjustedShiftDate}T${shiftOut}`);
    const actualOutDateTime = new Date(`${dateOut}T${timeOut}`);

    if (isNaN(shiftOutDateTime) || isNaN(actualOutDateTime)) {
        console.warn('Invalid date/time in computeOvertime', { shiftOutDateTime, actualOutDateTime });
        return 0;
    }

    const diffMs = actualOutDateTime - shiftOutDateTime;
    if (diffMs <= 0) return 0;

    const diffHours = diffMs / (1000 * 60 * 60); // total hours as decimal

    // Round down to nearest 0.5
    const hours = Math.floor(diffHours);           // full hours
    const minutes = (diffHours - hours) * 60;     // remaining minutes
    let overtime = hours;
    if (minutes >= 30) overtime += 0.5;

    if (overtime < 1) return 0;

    return overtime;
}

function calculateNightDiff(timeIn, timeOut) {

    let ndStart1 = new Date(timeIn);
    ndStart1.setHours(22, 0, 0, 0); // 22:00 of same day

    let ndEnd1 = new Date(timeIn);
    ndEnd1.setHours(24, 0, 0, 0); // still midnight

    let ndStart2 = new Date(timeIn);
    ndStart2.setDate(ndStart2.getDate() + 1); // next day
    ndStart2.setHours(0, 0, 0, 0); // 00:00 next day

    let ndEnd2 = new Date(ndStart2);
    ndEnd2.setHours(6, 0, 0, 0); // 06:00 next day

    // Helper function to get overlapping milliseconds between two intervals
    function getOverlap(startA, endA, startB, endB) {
        let start = startA > startB ? startA : startB;
        let end = endA < endB ? endA : endB;
        return Math.max(0, end - start);
    }

    let nd1Millis = getOverlap(timeIn, timeOut, ndStart1, ndEnd1);
    let nd2Millis = getOverlap(timeIn, timeOut, ndStart2, ndEnd2);

    // Total ND time in hours
    let totalNDHours = (nd1Millis + nd2Millis) / (1000 * 60 * 60);

    return {
        nd1: nd1Millis / (1000 * 60 * 60),
        nd2: nd2Millis / (1000 * 60 * 60),
        totalND: totalNDHours
    };
}

function roundToHalfMax8(num) {
    let rounded = Math.round(num * 2) / 2;
    return Math.min(rounded, 8);
}