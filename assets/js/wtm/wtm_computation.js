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
    ndStart1.setHours(22, 0, 0, 0);

    let ndEnd1 = new Date(timeIn);
    ndEnd1.setHours(24, 0, 0, 0);

    let ndStart2 = new Date(timeIn);
    ndStart2.setDate(ndStart2.getDate() + 1);
    ndStart2.setHours(0, 0, 0, 0);

    let ndEnd2 = new Date(ndStart2);
    ndEnd2.setHours(6, 0, 0, 0);

    function getOverlap(startA, endA, startB, endB) {
        let start = startA > startB ? startA : startB;
        let end = endA < endB ? endA : endB;
        return Math.max(0, end - start);
    }

    let nd1Millis = getOverlap(timeIn, timeOut, ndStart1, ndEnd1);
    let nd2Millis = getOverlap(timeIn, timeOut, ndStart2, ndEnd2);

    let nd1Hours = nd1Millis / (1000 * 60 * 60);
    let nd2Hours = nd2Millis / (1000 * 60 * 60);

    function adjustND(num) {
        if (num < 1) return 0;  // discard if less than 1 hr
        return Math.floor(num * 2) / 2; // round down to nearest 0.5
    }

    nd1Hours = adjustND(nd1Hours);
    nd2Hours = adjustND(nd2Hours);

    let totalND = nd1Hours + nd2Hours;
    if (totalND > 8) totalND = 8;

    return {
        nd1: nd1Hours,
        nd2: nd2Hours,
        totalND: totalND
    };
}


function roundToHalfMax8(num) {
    let rounded = Math.round(num * 2) / 2;
    return Math.min(rounded, 8);
}

async function GetHoliday(date) {
    const response = await fetch(`../fetch/wtm/wtm_check_date_ifholiday.php?date=${date}`);
    const text = await response.text();

    if (text.trim() === "") {
        return null; // no holiday
    }

    const parts = text.split("|");
    return { name: parts[0], type: parts[1] };
}

async function GetDTRRemarks(shiftdate, shiftcode, shiftin, shiftout, timein, timeout, TMH_Value, payrolltype) {
    let remdetails = '';
    let tcount = 0;

    const holiday = await GetHoliday(shiftdate);

    if (holiday) {
        remdetails = `${holiday.name} (${holiday.type})`;

        if (holiday.type === 'LEGAL') {
            if (payrolltype === 'DAILY') {
                if (timein === '' && timeout === '') {
                    tcount = 1.0;
                    TMH_Value = 0;
                } else {
                    tcount = 1.0;
                    TMH_Value = 8;
                }
            } else if (payrolltype === 'MONTHLY') {
                if (timein === '' || timeout === '') {
                    tcount = 0;
                    TMH_Value = 0;
                } else {
                    tcount = 1;
                    TMH_Value = 8;
                }
            } else {
                if (TMH_Value >= 8) {
                    tcount = 1.0;
                } else if (TMH_Value >= 4 && TMH_Value < 8) {
                    tcount = 0.5;
                } else {
                    tcount = 0;
                }
            }
        }
    } else {
        if (shiftin && shiftout && (timein === '' && timeout === '')) {
            remdetails = 'Absent';
        } else if (shiftcode === 'NS') {
            remdetails = 'No Schedule';
        } else if (shiftcode === '0DO' && (timein === '' || timeout === '')) {
            remdetails = 'Rest Day';
        } else {
            remdetails = '';
        }

        if (!timein || !timeout || timein === '' || timeout === '') {
            tcount = 0;
            TMH_Value = 0;
        } else {
            tcount = 1.0;
            TMH_Value = 8;
        }
            
    }

    return { remdetails, tcount, TMH_Value };
}

