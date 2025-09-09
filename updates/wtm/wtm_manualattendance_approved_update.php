<?php
include '../../config/connection.php';

try {
    $conn2->begin_transaction();

    // Collect POST data safely
    $requestid = $_POST['requestid'] ?? '';
    $employeeid = $_POST['employeeid'] ?? '';
    $employeename = $_POST['employeename'] ?? '';
    $department = $_POST['department'] ?? '';
    $area = $_POST['area'] ?? '';
    $position = $_POST['position'] ?? '';
    $dtrdate = $_POST['dtrdate'] ?? '';
    $dayOfWeek = $_POST['dayOfWeek'] ?? '';
    $shiftcode = $_POST['shiftcode'] ?? '';
    $shiftin = $_POST['shiftin'] ?? '';
    $shiftout = $_POST['shiftout'] ?? '';
    $datein = $_POST['datein'] ?? '';
    $dateout = $_POST['dateout'] ?? '';
    $timein = $_POST['timein'] ?? '';
    $timeout = $_POST['timeout'] ?? '';
    $tardiness_global = $_POST['tardiness_global'] ?? 0;
    $undertime_global = $_POST['undertime_global'] ?? 0;
    $overtime_global = $_POST['overtime_global'] ?? 0;
    $nightdiff_global = $_POST['nightdiff_global'] ?? 0;
    $nd_1012_global = $_POST['nd_1012_global'] ?? 0;
    $nd_1206_global = $_POST['nd_1206_global'] ?? 0;
    $remarks_global = $_POST['remarks_global'] ?? '';
    $totalmanhours_global = $_POST['totalmanhours_global'] ?? 0;
    $transactioncount_global = $_POST['transactioncount_global'] ?? 0;
    $approvaldecline_remarks = $_POST['approvaldecline_remarks'] ?? '';
    $status = $_POST['status'] ?? 'APPROVED';
    $datecreated_mysql = date('Y-m-d');
    $timecreated_mysql = date('Y-m-d H:i:s');
    $outright_in = $_POST['outright_in'] ?? '';
    $outright_out = $_POST['outright_out'] ?? '';

    $currentuser = $_POST['currentuser'] ?? '';

    // HEADER UPDATE
    $stmt = $conn2->prepare("UPDATE timekeeping_dtr_manualrequest SET status = ? WHERE requestid = ?");
    $stmt->bind_param("ss", $status, $requestid);
    if (!$stmt->execute()) {
        throw new Exception("Header Update Error: " . $stmt->error);
    }
    $stmt->close();

    // DATA UPDATE
    $stmt = $conn2->prepare("UPDATE timekeeping_dtr_data SET 
        datein = ?, timein = ?, dateout = ?, timeout = ?, 
        tardiness = ?, undertime = ?, overtime = ?, nightdiff = ?, 
        nd_1012 = ?, nd_1206 = ?, totalmanhours = ?, 
        remarks = ?, transactioncount = ?
        WHERE employeeid = ? AND date = ?");

    $stmt->bind_param(
        "ssssdddddisisss", 
        $datein, $timein, $dateout, $timeout,
        $tardiness_global, $undertime_global, $overtime_global, $nightdiff_global,
        $nd_1012_global, $nd_1206_global, $totalmanhours_global,
        $remarks_global, $transactioncount_global,
        $employeeid, $dtrdate
    );

    if (!$stmt->execute()) {
        throw new Exception("Data Update Error: " . $stmt->error);
    }
    $stmt->close();

    // Automated Remarks
    $datein_obj = new DateTime($datein);
    $datein_formatted = $datein_obj->format('M d, Y D');
    $dateout_obj = new DateTime($dateout);
    $dateout_formatted = $dateout_obj->format('M d, Y D');

    if ($outright_in == '1' && $outright_out == '1') {
        $Automated_Remarks = "Validated the request with Outright Time In: $datein_formatted $timein and Outright Time Out: $dateout_formatted $timeout.";
    } elseif ($outright_in == '1') {
        $Automated_Remarks = "Validated the request with Outright Time In: $datein_formatted $timein.";
    } elseif ($outright_out == '1') {
        $Automated_Remarks = "Validated the request with Outright Time Out: $dateout_formatted $timeout.";
    } else {
        $Automated_Remarks = "Validated the request without any outright in/out.";
    }

    $stmt2 = $conn2->prepare("INSERT INTO timekeeping_dtr_manualrequest_logs
        (request_id, employee_id, logs_description, action_remarks, request_status, action_by, action_date, action_time)
        VALUES (?,?,?,?,?,?,?,?)");
    $stmt2->bind_param(
        "ssssssss", 
        $requestid, $employeeid, $Automated_Remarks, 
        $approvaldecline_remarks, $status, 
        $currentuser, $datecreated_mysql, $timecreated_mysql
    );

    if (!$stmt2->execute()) {
        throw new Exception("Log Insert Error: " . $stmt2->error);
    }
    $stmt2->close();

    // Commit transaction
    $conn2->commit();
    echo "Success";

} catch (Exception $e) {
    $conn2->rollback();
    echo $e->getMessage();
} finally {
    $conn2->close();
}
?>
