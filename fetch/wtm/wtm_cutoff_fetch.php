<?php
require_once '../../config/connection.php';

$stmt = $conn2->prepare("CALL WEB_DTR_DROPDOWN_CUTOFF()");
$stmt->execute();
$result = $stmt->get_result();

$options = '<option value="" disabled selected>Select Cutoff...</option>';

while ($row = $result->fetch_assoc()) {
    $cutoff = htmlspecialchars($row['cutoff']);
    $cutoffFrom = htmlspecialchars($row['cutoffdatefrom']);
    $cutoffTo = htmlspecialchars($row['cutoffdateto']);

    $options .= "<option 
        value=\"$cutoff\" 
        data-co-from=\"$cutoffFrom\" 
        data-co-to=\"$cutoffTo\">$cutoff</option>";
}

$stmt->close();
$conn2->next_result();

echo $options;
