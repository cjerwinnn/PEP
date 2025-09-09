<?php
include '../../config/connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$employee_id = $_POST['employee_id'] ?? null;
$overtime_id = $_POST['overtime_id'] ?? null;

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

if ($employee_id) {
    $stmt = $conn2->prepare("CALL WEB_OT_DETAILS(?)");
    $stmt->bind_param('s', $overtime_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $defaultPicturePath = '../../assets/imgs/user_default.png';
        $pictureData = !empty($row['picture']) ? $row['picture'] : file_get_contents($defaultPicturePath);

        // Store in session

        $_SESSION['overtimeid'] = $row['overtimeid'];
        $_SESSION['applicationtype'] = $row['applicationtype'];
        $_SESSION['overtimedate'] = $row['overtimedate'];
        $_SESSION['overtimestart'] = $row['overtimestart'];
        $_SESSION['overtimeend'] = $row['overtimeend'];
        $_SESSION['totalovertime'] = $row['totalovertime'];
        $_SESSION['overtimetype'] = $row['overtimetype'];
        $_SESSION['reason'] = $row['reason'];
        $_SESSION['status'] = $row['status'];
        $_SESSION['datecreated'] = $row['datecreated'];
        $_SESSION['timecreated'] = $row['timecreated'];
        $_SESSION['shiftcode'] = $row['shiftcode'];
        $_SESSION['shiftstart'] = $row['shiftstart'];
        $_SESSION['shiftend'] = $row['shiftend'];
        $_SESSION['datein'] = $row['datein'];
        $_SESSION['timein'] = $row['timein'];
        $_SESSION['dateout'] = $row['dateout'];
        $_SESSION['timeout'] = $row['timeout'];
        $_SESSION['excess_hours'] = $row['excess_hours'];
        $_SESSION['overtimetype'] = $row['overtimetype'];

        // Echo a simple success message
        echo "Overtime info loaded successfully.";
    } else {
        echo "No Overtime records found.";
    }
    $stmt->close();
}

if ($overtime_id) {
    $stmtAttachment = $conn2->prepare("CALL WEB_OT_ATTACHMENTS(?)");
    $stmtAttachment->bind_param('s', $overtime_id);
    $stmtAttachment->execute();
    $resultAttachments = $stmtAttachment->get_result();

    $AttachmentsData = []; // Array to hold all attachments
    if ($resultAttachments && $resultAttachments->num_rows > 0) {
        while ($rowItem = $resultAttachments->fetch_assoc()) {
            $AttachmentsData[] = $rowItem; // store each row as array element
        }
    }

    // Store the attachments array in session
    $_SESSION['AttachmentsData'] = $AttachmentsData;

    $stmtAttachment->close();
}

if ($overtime_id) {

    $stmtLogs = $conn2->prepare("CALL WEB_OT_LOGS(?)");
    if (!$stmtLogs) {
        die("Prepare failed: " . $conn2->error);
    }

    $stmtLogs->bind_param('s', $overtime_id);

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
