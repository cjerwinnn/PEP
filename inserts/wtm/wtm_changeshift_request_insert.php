<?php
include '../../config/connection.php'; // your DB connection

// Collect POST data
$requestid = $_POST['requestid'];
$month = (int)$_POST['month'];
$year = (int)$_POST['year'];
$employeeid = $_POST['employeeid'];
$shiftdate = $_POST['shiftdate']; 
$currentshiftcode = $_POST['currentshiftcode'];
$currentshiftsched = $_POST['currentshiftsched'];
$newshiftcode = $_POST['newshiftcode'];
$newshiftsched = $_POST['newshiftsched'];
$reason = $_POST['reason'];
$status = $_POST['status'];
$datecreated_mysql = date('Y-m-d'); // Only the date
$timecreated_mysql = date('Y-m-d H:i:s');

// Prepare statement
$stmt = $conn2->prepare("INSERT INTO timekeeping_shiftschedule_requestfiled 
(requestid, month, year, employeeid, shiftdate, currentshiftcode, currentshiftsched, newshiftcode, newshiftsched, reason, status, datecreated, timecreated) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

// Bind parameters
// s = string, i = integer
$stmt->bind_param(
    "siissssssssss",
    $requestid,
    $month,
    $year,
    $employeeid,
    $shiftdate,
    $currentshiftcode,
    $currentshiftsched,
    $newshiftcode,
    $newshiftsched,
    $reason,
    $status,
    $datecreated_mysql,
    $timecreated_mysql
);

// Execute statement
if ($stmt->execute()) {
    echo "Success";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn2->close();
