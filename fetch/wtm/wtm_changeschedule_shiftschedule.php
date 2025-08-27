<?php
require '../../config/connection.php'; // adjust path to your DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $area = $_POST['area'] ?? '';

    try {
        $stmt = $conn2->prepare("CALL WEB_SC_SCHEDULE_LIST(?)");
        $stmt->bind_param("s", $area);
        $stmt->execute();
        $result = $stmt->get_result();

        $schedules = [];
        while ($row = $result->fetch_assoc()) {
            $schedules[] = $row;
        }

        echo json_encode($schedules);
    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}
