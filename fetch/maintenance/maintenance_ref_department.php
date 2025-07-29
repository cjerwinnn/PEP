<?php
include '../../config/connection.php';

// No parameters needed since it fetches all departments
$stmt = $conn->prepare("CALL REF_DEPARTMENT()");
$stmt->execute();
$result = $stmt->get_result();

$options = '<option value="" selected>***Please select the department***</option>';
while ($row = $result->fetch_assoc()) {
    $department = htmlspecialchars($row['department']);
    $options .= "<option value=\"$department\">$department</option>";
}

$stmt->close();
$conn->next_result();

echo $options;
