<?php
include '../../config/connection.php';

$request_id = $_POST['request_id'] ?? '';
$requestor_EmployeeID = $_POST['requestor_EmployeeID'] ?? '';
$approved_overtime_hours = $_POST['approved_overtime_hours'] ?? '';
$tagged_by = $_POST['tagged_by'] ?? '';
$request_status = $_POST['request_status'] ?? 'APPROVED'; // default
$approver_level = $_POST['approver_level'] ?? '';
$approval_remarks = $_POST['approval_remarks'] ?? '';
$req_status = 'APPROVED';

$datecreated_mysql = date('Y-m-d');
$timecreated_mysql = date('Y-m-d H:i:s');

$level_status = 'Level [' . $approver_level . '] ' . $req_status;

try {
    $conn2->begin_transaction();

    $stmt = $conn2->prepare("CALL WEB_OT_APPROVAL_CHECKCOUNT(?)");
    $stmt->bind_param("s", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $count_notApproved = $row['not_approved_count'];
    $result->free();
    $stmt->close();

    if ($count_notApproved == 1) {
        $stmt1 = $conn2->prepare("UPDATE timekeeping_overtime_filed SET status = 'APPROVED' WHERE overtimeid = ?");
        $stmt1->bind_param("s", $request_id);
        $stmt1->execute();

        $Automated_Remarks = 'All Approvers have approved the request with Approved Overtime hour(s) of ' . $approved_overtime_hours . '.';
        $Automated_taggedby = 'System Generated.';

        $stmt2 = $conn2->prepare("INSERT INTO timekeeping_overtime_approvedecline
            (employeeid, overtimeid, status, remarks, approved_hours, datetagged, timetagged, taggedby)
            VALUES (?,?,?,?,?,?,?,?)");
        $stmt2->bind_param("ssssssss", $requestor_EmployeeID, $request_id, $req_status, $Automated_Remarks, $approved_overtime_hours, $datecreated_mysql, $timecreated_mysql, $tagged_by);
        $stmt2->execute();

        $stmt3 = $conn2->prepare("INSERT INTO timekeeping_overtime_logs
            (overtime_id, employee_id, approved_hours, action_remarks, request_status, action_by, action_date, action_time)
            VALUES (?,?,?,?,?,?,?,?)");
        $stmt3->bind_param("ssssssss", $request_id, $requestor_EmployeeID, $approved_overtime_hours, $Automated_Remarks, $req_status, $Automated_taggedby, $datecreated_mysql, $timecreated_mysql);
        $stmt3->execute();
    }

    $stmt1 = $conn2->prepare("UPDATE timekeeping_overtime_approvals_details SET tagged_remarks = ?, tagged_status = ?, tagged_date = ?, tagged_time = ? WHERE request_id = ? AND approver_employeeid = ?");
    $stmt1->bind_param("ssssss", $approval_remarks, $req_status, $datecreated_mysql, $timecreated_mysql, $request_id, $tagged_by);
    $stmt1->execute();

    // LOGS
    $stmt2 = $conn2->prepare("INSERT INTO timekeeping_overtime_logs
            (overtime_id, employee_id, approved_hours, action_remarks, request_status, action_by, action_date, action_time)
            VALUES (?,?,?,?,?,?,?,?)");
    $stmt2->bind_param("ssssssss", $request_id, $requestor_EmployeeID, $approved_overtime_hours ,$approval_remarks, $level_status, $tagged_by, $datecreated_mysql, $timecreated_mysql);
    $stmt2->execute();

    $conn2->commit();

    echo "Success";
} catch (Exception $e) {
    $conn2->rollback();
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
