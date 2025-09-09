<?php
include '../../config/connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json'); // Return JSON

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method. Only POST is allowed.']);
    exit;
}

$employee_id = $_POST['employee_id'] ?? null;
if (!$employee_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing employee_id']);
    exit;
}

$stmt_employee = $conn2->prepare("CALL WEB_EMPLOYEE_HEADER(?)");
if (!$stmt_employee) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to prepare statement: ' . $conn2->error]);
    exit;
}

$stmt_employee->bind_param('s', $employee_id);

if (!$stmt_employee->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to execute statement: ' . $stmt_employee->error]);
    $stmt_employee->close();
    exit;
}


$result_employee = $stmt_employee->get_result();

if ($result_employee && $result_employee->num_rows > 0) {
    $row_employee = $result_employee->fetch_assoc();

    $defaultPicturePath = '../../assets/imgs/user_default.png';
    $pictureData = !empty($row_employee['picture']) ? $row_employee['picture'] : file_get_contents($defaultPicturePath);

    $_SESSION['form_picture'] = $pictureData;

    $_SESSION['employee_id']   = $row_employee['employeeid'];
    $_SESSION['employeename']  = $row_employee['employeename'];
    $_SESSION['lastname']      = $row_employee['lastname'];
    $_SESSION['firstname']     = $row_employee['firstname'];
    $_SESSION['middlename']    = $row_employee['middlename'];
    $_SESSION['suffix']        = $row_employee['suffix'];
    $_SESSION['department']    = $row_employee['department'];
    $_SESSION['area']          = $row_employee['area'];
    $_SESSION['position']      = $row_employee['position'];

    $response = $row_employee;
    unset($response['picture']);

    echo json_encode($response);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'No records found']);
}

if ($result_employee) $result_employee->free();
while ($conn2->more_results() && $conn2->next_result()) {
    $conn2->use_result();
}
$stmt_employee->close();
