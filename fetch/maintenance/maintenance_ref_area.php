<?php
include '../../config/connection.php';  // Your DB connection

// Assume s_dept is passed via POST or GET, sanitize accordingly
$s_dept = $_POST['department'] ?? '';

if (!$s_dept) {
    echo '<option value="">No department selected</option>';
    exit;
}

$stmt = $conn->prepare("CALL REF_AREA(?)");
$stmt->bind_param("s", $s_dept);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $area = htmlspecialchars($row['area']);
    $options .= "<option value=\"$area\">$area</option>";
}

$stmt->close();
$conn->next_result();

echo $options;
