<?php
require '../../config/connection.php'; // adjust path to your DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        $stmt = $conn2->prepare("CALL WEB_OT_TYPE_LIST");
        $stmt->execute();
        $result = $stmt->get_result();

        $ot_types = [];
        while ($row = $result->fetch_assoc()) {
            $ot_types[] = $row;
        }

        echo json_encode($ot_types);
    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}
