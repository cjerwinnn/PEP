<?php
include '../config/connection.php'; // your DB connection file

$employee_id = $_POST['employee_id'] ?? '';
$input_password = $_POST['password'] ?? '';

if (empty($employee_id) || empty($input_password)) {
    echo 'missing';
    exit;
}

// Call stored procedure
$stmt = $conn->prepare("CALL SYSTEM_V_ACCESS(?)");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {
    $vaultPassword = $row['v_pass'];

    if ($input_password === $vaultPassword) {
        echo 'success';
    } else {
        echo 'invalid';
    }
    
} else {
    echo 'error';
}
$stmt->close();
$conn->close();
