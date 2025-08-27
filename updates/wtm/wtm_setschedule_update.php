<?php
session_start();
include '../../config/connection.php';

// Expecting arrays from JS
$employeeid   = $_POST['employeeid'] ?? '';
$dates        = $_POST['dates'] ?? [];        // array of YYYY-MM-DD
$shiftcodes   = $_POST['shiftcodes'] ?? [];   // array of shiftcodes
$holidays     = $_POST['holidays'] ?? [];     // array of holiday names

if (empty($employeeid) || empty($dates)) {
    echo "Missing required data.";
    exit;
}

// Prepare UPDATE statement
$sql = "UPDATE timekeeping_emp_sched 
        SET 
            `month` = MONTH(?),
            `day` = DAY(?),
            `year` = YEAR(?),
            dayname = UPPER(DATE_FORMAT(?, '%a')),
            shiftcode = ?,
            IsHoliday = CASE WHEN ? <> '' THEN 1 ELSE 0 END,
            HolidayRemarks = ?
        WHERE employeeid = ? AND `date` = ?";

$stmt = $conn2->prepare($sql);

if (!$stmt) {
    echo "Prepare failed: " . $conn2->error;
    exit;
}

// Loop through dates
for ($i = 0; $i < count($dates); $i++) {
    $date        = $dates[$i];
    $shiftcode   = $shiftcodes[$i] ?? 'NS';
    $holidayname = $holidays[$i] ?? '';

    $stmt->bind_param(
        "sssssssss",
        $date,       // for MONTH()
        $date,       // for DAY()
        $date,       // for YEAR()
        $date,       // for dayname
        $shiftcode,  // shiftcode
        $holidayname, // for IsHoliday check
        $holidayname, // HolidayRemarks
        $employeeid, // WHERE employeeid = ?
        $date        // WHERE date = ?
    );
    $stmt->execute();
}

$stmt->close();
$conn2->close();

echo "All schedules updated successfully.";
