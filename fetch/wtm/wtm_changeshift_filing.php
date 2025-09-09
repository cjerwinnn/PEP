<?php
include '../../config/connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$employee_id = $_POST['employee_id'] ?? null;
$shiftdate = $_POST['shiftdate'] ?? null;
$_SESSION['shiftdate'] = $shiftdate;

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
        $area = $row['area'];

        // Echo a simple success message
        echo "Employee info loaded successfully.";
    } else {
        echo "No employee records found.";
    }
    $stmt->close();
}

if ($shiftdate) {
    $stmt = $conn2->prepare("CALL WEB_CS_SHIFT_DETAILS(?,?)");
    $stmt->bind_param('ss', $employee_id, $shiftdate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $_SESSION['shiftcode'] = $row['shiftcode'];
        $_SESSION['shiftstart'] = $row['shiftstart'];
        $_SESSION['shiftend'] = $row['shiftend'];
        $_SESSION['datein'] = $row['datein'];
        $_SESSION['timein'] = $row['timein'];
        $_SESSION['dateout'] = $row['dateout'];
        $_SESSION['timeout'] = $row['timeout'];

        echo "Shift Schedule info loaded successfully.";
    } else {
        echo "No Shift Schedule records found." . $employee_id . ' ' . $shiftdate;
    }
    $stmt->close();
}

if ($area) {

    $stmtLogs = $conn2->prepare("CALL WEB_SC_SCHEDULE_LIST(?)");
    if (!$stmtLogs) {
        die("Prepare failed: " . $conn2->error);
    }

    $stmtLogs->bind_param('s', $area);

    if ($stmtLogs->execute()) {
        $resultAttachments = $stmtLogs->get_result();

        // Initialize an array to hold item data
        $ScheduleList = [];
        if ($resultAttachments->num_rows > 0) {
            while ($rowItem = $resultAttachments->fetch_assoc()) {
                $ScheduleList[] = $rowItem;
            }
        }
        $_SESSION['ScheduleList'] = $ScheduleList;
    } else {
        die("Execute failed: " . $stmtLogs->error);
    }

    $stmtLogs->close();
}


$conn2->close();
