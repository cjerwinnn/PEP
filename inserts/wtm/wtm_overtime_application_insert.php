<?php
include '../../config/connection.php'; // your DB connection

// Collect POST data
$overtimeid = $_POST['overtimeid'];
$month = (int)$_POST['month'];
$year = (int)$_POST['year'];
$applicationtype = $_POST['applicationtype'];
$employeeid = $_POST['employeeid'];
$emp_area = $_POST['emp_area'];
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

try {
    // Start transaction
    $conn2->begin_transaction();

    // Insert into overtime filed
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

    if (!$stmt->execute()) {
        throw new Exception("Error inserting overtime filed: " . $stmt->error);
    }

    $stmt->close();

    // Call approval flow stored procedure
    $stmtFlow = $conn2->prepare("CALL WEB_OT_APPROVAL_FLOW(?)");
    $stmtFlow->bind_param("s", $emp_area);

    if (!$stmtFlow->execute()) {
        throw new Exception("Error executing approval flow: " . $stmtFlow->error);
    }

    $resultFlow = $stmtFlow->get_result();
    $approvers = [];
    while ($row = $resultFlow->fetch_assoc()) {
        $approvers[] = $row;
    }

    $resultFlow->free();
    $stmtFlow->close();

    // Insert approval details
    $insertStmt = $conn2->prepare("INSERT INTO timekeeping_overtime_approvals_details (
        request_id, approver_employeeid, approver_level, override_access, 
        tagged_remarks, tagged_status, tagged_date, tagged_time) 
        VALUES (?, ?, ?, ?, '', 'PENDING', NULL, NULL)");

    foreach ($approvers as $row) {
        $approver_id = $row['approvers_employeeid'];
        $approver_level = $row['approver_level'];
        $override_access = $row['override_access'];

        $insertStmt->bind_param("ssii", $overtimeid, $approver_id, $approver_level, $override_access);
        if (!$insertStmt->execute()) {
            throw new Exception("Error inserting approval details: " . $insertStmt->error);
        }
    }

    $insertStmt->close();

    $Automated_Remarks = 'Overtime ID: [' . $overtimeid . '] with ' . $totalovertime . ' hour(s) of overtime has been filed.';
    $Automated_taggedby = 'System Generated.';
    $approved_overtime_hours = 0;

    $stmt2 = $conn2->prepare("INSERT INTO timekeeping_overtime_logs
            (overtime_id, employee_id, approved_hours, action_remarks, request_status, action_by, action_date, action_time)
            VALUES (?,?,?,?,?,?,?,?)");
    $stmt2->bind_param("ssssssss", $overtimeid, $employeeid, $approved_overtime_hours, $Automated_Remarks, $status, $Automated_taggedby, $datecreated_mysql, $timecreated_mysql);
    $stmt2->execute();

    // Commit transaction
    $conn2->commit();

    echo "Success";
} catch (Exception $e) {
    // Rollback if any error occurs
    $conn2->rollback();
    echo "Transaction failed: " . $e->getMessage();
}

$conn2->close();
