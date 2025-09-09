<?php
include '../../config/connection.php'; // your DB connection

$conn2->begin_transaction();

// Collect POST data
$requestid = $_POST['requestid'];
$employeeid = $_POST['employeeid'];
$employeename = $_POST['employeename'];
$department = $_POST['department'];
$area = $_POST['area'];
$position = $_POST['position'];
$dtrdate = $_POST['dtrdate'];
$dayOfWeek = $_POST['dayOfWeek'];
$shiftcode = $_POST['shiftcode'];
$shiftin = $_POST['shiftin'];
$shiftout = $_POST['shiftout'];
$datein = $_POST['datein'];
$dateout = $_POST['dateout'];
$timein = $_POST['timein'];
$timeout = $_POST['timeout'];
$tardiness_global = $_POST['tardiness_global'];
$undertime_global = $_POST['undertime_global'];
$overtime_global = $_POST['overtime_global'];
$nightdiff_global = $_POST['nightdiff_global'];
$nd_1012_global = $_POST['nd_1012_global'];
$nd_1206_global = $_POST['nd_1206_global'];
$remarks_global = $_POST['remarks_global'];
$totalmanhours_global = $_POST['totalmanhours_global'];
$transactioncount_global = $_POST['transactioncount_global'];

$reason = $_POST['reason'];
$status = $_POST['status'];
$datecreated_mysql = date('Y-m-d');
$timecreated_mysql = date('Y-m-d H:i:s');

$currentuser = $_POST['currentuser'];


$default_data = null;

// HEADER
$stmt = $conn2->prepare("INSERT INTO timekeeping_dtr_manualrequest 
(requestid, employeeid, dtrdate, daterequested, timerequested, reason, approveddecline_remarks, approveddate, approvedtime, approvedby, status) 
VALUES (?,?,?,?,?,?,?,?,?,?,?)");

$stmt->bind_param(
    "sssssssssss",
    $requestid,
    $employeeid,
    $dtrdate,
    $datecreated_mysql,
    $timecreated_mysql,
    $reason,
    $default_data,
    $default_data,
    $default_data,
    $default_data,
    $status
);

if ($stmt->execute()) {
    echo "Success";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();

// DATA
$stmt = $conn2->prepare("INSERT INTO timekeeping_dtr_manualrequest_data 
(employeeid, employeename, department, area, date, dayofweek, shiftcode, shiftin, shiftout, datein, timein, dateout, timeout, tardiness, undertime, overtime, nightdiff, nd_1012, nd_1206, totalmanhours, remarks, transactioncount, requestid) 
VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

$stmt->bind_param(
    "sssssssssssssssssssssss",
    $employeeid,
    $employeename,
    $department,
    $area,
    $dtrdate,
    $dayOfWeek,
    $shiftcode,
    $shiftin,
    $shiftout,
    $datein,
    $timein,
    $dateout,
    $timeout,
    $tardiness_global,
    $undertime_global,
    $overtime_global,
    $nightdiff_global,
    $nd_1012_global,
    $nd_1206_global,
    $totalmanhours_global,
    $remarks_global,
    $transactioncount_global,
    $requestid
);

if ($stmt->execute()) {
    echo "Success";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();

$datein_obj = new DateTime($datein);
$datein_formatted = $datein_obj->format('M d, Y D');
$dateout_obj = new DateTime($dateout);
$dateout_formatted = $dateout_obj->format('M d, Y D');

$Automated_Remarks = 'Request ID: [' . $requestid . '] with Requested Manual In: ' . $datein_formatted . ' ' . $timein . ' and Requested Manual Out: ' . $dateout_formatted . ' ' . $timeout . '.';
$Automated_taggedby = 'System Generated.';

$action_remarks = 'N/A';

$stmt2 = $conn2->prepare("INSERT INTO timekeeping_dtr_manualrequest_logs
            (request_id, employee_id, logs_description, action_remarks, request_status, action_by, action_date, action_time)
            VALUES (?,?,?,?,?,?,?,?)");
$stmt2->bind_param("ssssssss", $requestid, $employeeid, $Automated_Remarks, $action_remarks, $status, $Automated_taggedby, $datecreated_mysql, $timecreated_mysql);
$stmt2->execute();

// Commit transaction
$conn2->commit();

$conn2->close();
