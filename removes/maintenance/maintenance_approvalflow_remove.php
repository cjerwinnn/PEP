<?php
include '../../config/connection.php';

$area = $_POST['area'] ?? '';
$level = $_POST['level'] ?? '';
$empid = $_POST['empid'] ?? '';

if (!$area) {
    http_response_code(400);
    echo 'AREA';
    exit;
}

if (!$level) {
    http_response_code(400);
    echo 'LEVEL';
    exit;
}


if (!$empid) {
    http_response_code(400);
    echo 'ID';
    exit;
}


$conn->begin_transaction();

try {
    // 1. Delete the specific level approver for the area
    $stmtDel = $conn->prepare("DELETE FROM config_approval_flow WHERE area = ? AND approver_level = ? AND approvers_employeeid = ?");
    $stmtDel->bind_param("sis", $area, $level, $empid);
    $stmtDel->execute();

    if ($stmtDel->affected_rows === 0) {
        throw new Exception("No record deleted at level $level.");
    }

    // 2. Shift down all levels > deleted level by 1
    $stmtUpdate = $conn->prepare("UPDATE config_approval_flow 
                                  SET approver_level = approver_level - 1 
                                  WHERE area = ? AND approver_level > ?");
    $stmtUpdate->bind_param("si", $area, $level);
    $stmtUpdate->execute();

    $conn->commit();
    echo 'success';

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo 'Error: ' . $e->getMessage();
}

$stmtDel->close();
$stmtUpdate->close();
$conn->close();
?>
