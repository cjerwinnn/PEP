<?php
include_once '../../config/connection.php';
session_start();

$month = $_GET['month'];
$year = $_GET['year'];

// Get selected statuses from GET request (optional)
$statusList = isset($_GET['statuses']) ? $_GET['statuses'] : null;
if ($statusList) $statusList = str_replace(', ', ',', $statusList);

// Call stored procedure with optional status filter
if ($statusList) {
    $stmt = $conn2->prepare("CALL WEB_MIO_VALIDATION_LIST(?,?,?)");
    $stmt->bind_param('iis', $month, $year,  $statusList);
} else {
    $stmt = $conn2->prepare("CALL WEB_MIO_VALIDATION_LIST(?,?,NULL)");
        $stmt->bind_param('ii', $month, $year);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $date = new DateTime($row['daterequested']);
    $formattedDate = $date->format('M d, Y');
    $dtrdate = new DateTime($row['dtrdate']);
    $dtrdate_formatted = $dtrdate->format('M d, Y');

    echo "<tr>";
    echo "<td class='text-start'>{$row['requestid']}</td>";
    echo "<td class='text-start'>{$row['employeeid']}</td>";
    echo "<td class='text-start'>{$row['employeename']}</td>";
    echo "<td class='text-start'>{$row['department']}</td>";
    echo "<td class='text-start'>{$row['area']}</td>";
    echo "<td class='text-start'>{$row['position']}</td>";
    echo "<td class='text-start'>{$dtrdate_formatted}</td>";
    echo "<td>{$formattedDate} {$row['timerequested']}</td>";
    echo "<td class='text-start'>{$row['status']}</td>";

    // Action buttons
    echo "<td class='text-center'><div class='d-flex justify-content-center gap-2'>";
    echo "<button class='btn btn-outline-secondary btn-sm' title='View General Voucher Details' onclick=\"ViewAttendance('{$row['employeeid']}', '{$row['requestid']}')\"><i class='bi bi-eye'></i></button>";
    echo "</div></td>";
    echo "</tr>";
}
$stmt->close();
