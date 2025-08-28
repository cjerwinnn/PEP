<?php
include '../../config/connection.php'; // your DB connection

// Collect POST data
$overtimeid = $_POST['overtimeid'];
$month = (int)$_POST['month'];
$year = (int)$_POST['year'];
$applicationtype = $_POST['applicationtype'];
$employeeid = $_POST['employeeid'];
$overtimedate = $_POST['overtimedate']; 
$overtimestart = $_POST['overtimestart'];
$overtimeend = $_POST['overtimeend'];
$nextday = $_POST['nextday'];
$totalovertime = $_POST['totalovertime'];
$overtimetype = $_POST['overtimetype'];
$reason = $_POST['reason'];
$status = $_POST['status'];
$datecreated_mysql = date('Y-m-d');
$timecreated_mysql = date('Y-m-d H:i:s');

// Prepare statement
$stmt = $conn2->prepare("INSERT INTO timekeeping_overtime_filed 
(overtimeid, month, year, applicationtype, employeeid, overtimedate, overtimestart, overtimeend, nextday, totalovertime, overtimetype, reason, status, datecreated, timecreated) 
VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

$stmt->bind_param(
    "siisssssissssss", 
    $overtimeid,
    $month,
    $year,
    $applicationtype,
    $employeeid,
    $overtimedate,
    $overtimestart,
    $overtimeend,
    $nextday,
    $totalovertime,
    $overtimetype,
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
