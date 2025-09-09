<?php
include '../../config/connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$employee_id = $_POST['employee_id'] ?? null;
$request_id = $_POST['request_id'] ?? null;

if ($employee_id) {
    $stmt = $conn2->prepare("CALL WEB_EMPLOYEE_HEADER(?)");
    $stmt->bind_param('s', $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $defaultPicturePath = '../../assets/imgs/user_default.png';
        $pictureData = !empty($row['picture']) ? $row['picture'] : file_get_contents($defaultPicturePath);

        // Store in session
        $_SESSION['form_picture'] = $pictureData;
        $_SESSION['employee_id'] = $row['employeeid'];
        $_SESSION['employeename'] = $row['employeename'];
        $_SESSION['lastname'] = $row['lastname'];
        $_SESSION['firstname'] = $row['firstname'];
        $_SESSION['middlename'] = $row['middlename'];
        $_SESSION['suffix'] = $row['suffix'];
        $_SESSION['department'] = $row['department'];
        $_SESSION['area'] = $row['area'];
        $_SESSION['position'] = $row['position'];

        // Echo a simple success message
        echo "Employee info loaded successfully.";
    } else {
        echo "No employee records found.";
    }
    $stmt->close();
}

if ($request_id) {
    $stmt = $conn2->prepare("CALL WEB_MIO_DETAILS(?)");
    $stmt->bind_param('s', $request_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $defaultPicturePath = '../../assets/imgs/user_default.png';
        $pictureData = !empty($row['picture']) ? $row['picture'] : file_get_contents($defaultPicturePath);

        // Store in session

        $_SESSION['requestid'] = $row['requestid'];
        $_SESSION['dtrdate'] = $row['dtrdate'];
        $_SESSION['reason'] = $row['reason'];
        $_SESSION['approveddecline_remarks'] = $row['approveddecline_remarks'];
        $_SESSION['approveddate'] = $row['approveddate'];
        $_SESSION['approvedtime'] = $row['approvedtime'];
        $_SESSION['approvedby'] = $row['approvedby'];
        $_SESSION['status'] = $row['status'];

        $_SESSION['current_datein'] = $row['current_datein'];
        $_SESSION['current_timein'] = $row['current_timein'];
        $_SESSION['current_dateout'] = $row['current_dateout'];
        $_SESSION['current_timeout'] = $row['current_timeout'];
        $_SESSION['shiftdate'] = $row['shiftdate'];
        $_SESSION['shiftcode'] = $row['shiftcode'];
        $_SESSION['shiftin'] = $row['shiftin'];
        $_SESSION['shiftout'] = $row['shiftout'];
        $_SESSION['datein'] = $row['datein'];
        $_SESSION['timein'] = $row['timein'];
        $_SESSION['dateout'] = $row['dateout'];
        $_SESSION['timeout'] = $row['timeout'];
        $_SESSION['tardiness'] = $row['tardiness'];
        $_SESSION['undertime'] = $row['undertime'];
        $_SESSION['nightdiff'] = $row['nightdiff'];
        $_SESSION['excess'] = $row['excess'];
        $_SESSION['totalmanhours'] = $row['totalmanhours'];
        $_SESSION['transactioncount'] = $row['transactioncount'];
        $_SESSION['remarks'] = $row['remarks'];

        // Echo a simple success message
        echo "Manual In/Out info loaded successfully.";
    } else {
        echo "No Overtime records found.";
    }
    $stmt->close();
}

if ($request_id) {

    $stmtLogs = $conn2->prepare("CALL WEB_MIO_LOGS(?)");
    if (!$stmtLogs) {
        die("Prepare failed: " . $conn2->error);
    }

    $stmtLogs->bind_param('s', $request_id);

    if ($stmtLogs->execute()) {
        $resultAttachments = $stmtLogs->get_result();

        // Initialize an array to hold item data
        $LogsData = [];
        if ($resultAttachments->num_rows > 0) {
            while ($rowItem = $resultAttachments->fetch_assoc()) {
                $LogsData[] = $rowItem;
            }
        }
        $_SESSION['LogsData'] = $LogsData;
    } else {
        die("Execute failed: " . $stmtLogs->error);
    }

    $stmtLogs->close();
}


$conn2->close();
