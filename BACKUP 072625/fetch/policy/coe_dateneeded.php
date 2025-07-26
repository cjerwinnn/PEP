<?php
include '../../config/connection.php';

// Get coe_type from request
$coe_type = $_POST['coe_type']; // default to TRAVEL if none provided

// Prepare and bind to avoid SQL injection
$stmt = $conn->prepare("SELECT policy_days FROM policy_coe_dateneeded WHERE coe_type = ? LIMIT 1");
$stmt->bind_param("s", $coe_type);
$stmt->execute();
$stmt->bind_result($policy_days);

if ($stmt->fetch()) {
    echo $policy_days;
} else {
    echo 0; // fallback if not found
}

$stmt->close();
$conn->close();
?>
