<?php
include '../../config/connection.php';

$area = $_POST['area'] ?? '';

if (!$area) {
    http_response_code(400);
    echo 'Missing area';
    exit;
}

$stmt = $conn->prepare("CALL MAINTENANCE_COE_APPROVAL_FLOW(?)");
$stmt->bind_param("s", $area);
$stmt->execute();
$result = $stmt->get_result();

$rows = '';

while ($row = $result->fetch_assoc()) {
    $level = htmlspecialchars($row['approver_level']);
    $approvers_employeeid = htmlspecialchars($row['approvers_employeeid']);
    $approver = htmlspecialchars($row['approver_name']);
    $department = htmlspecialchars($row['approver_department']);
    $approver_area = htmlspecialchars($row['approver_area']);
    $position = htmlspecialchars($row['approver_position']);
    $override = htmlspecialchars($row['override_access']);

    if ($override == '1') {
        $override = '<i class="bi bi-check-circle-fill text-success"></i>';
    } else {
        $override = '<i class="bi bi-x-circle-fill text-danger"></i>';
    }

    // You can customize the action column with buttons or icons
    $action = "<button class='btn btn-sm btn-danger rounded-4 btn-remove' 
            data-empid='" . htmlspecialchars($approvers_employeeid) . "'
            data-area='" . htmlspecialchars($area) . "'
            data-level='" . htmlspecialchars($level) . "'>
              Remove
           </button>";

    $rows .= "<tr class='text-center'>
                <td class='text-center text-dark fw-bold'>$level</td>
                <td class='text-start'>$approver</td>
                <td class='text-center'>$department</td>
                <td class='text-center'>$approver_area</td>
                <td class='text-center'>$position</td>
                <td>$override</td>
                <td>$action</td>
              </tr>";
}

$stmt->close();
$conn->next_result();

echo $rows;
