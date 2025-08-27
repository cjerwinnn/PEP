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

// Prepare SQL once
$sql = "INSERT INTO timekeeping_emp_sched 
        (employeeid, `date`, `month`, `day`, `year`, dayname, shiftcode, IsHoliday, HolidayRemarks)
        VALUES (?, ?, MONTH(?), DAY(?), YEAR(?), UPPER(DATE_FORMAT(?, '%a')), ?, CASE WHEN ? <> '' THEN 1 ELSE 0 END, ?)";
        
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
        $employeeid,
        $date,
        $date,
        $date,
        $date,
        $date,
        $shiftcode,
        $holidayname,
        $holidayname
    );
    $stmt->execute();
}

$stmt->close();
$conn2->close();

echo "All schedules saved successfully.";
