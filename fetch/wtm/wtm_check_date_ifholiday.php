<?php
include '../../config/connection.php';

if (isset($_GET['date'])) {
    $date = $_GET['date'];

    if ($conn2->connect_error) {
        die("DB connection failed: " . $conn2->connect_error);
    }

    $stmt = $conn2->prepare("CALL WEB_SC_CHECK_IFHOLIDAY(?)");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo $row['holiday'] . "|" . $row['type']; // no JSON
    } else {
        echo ""; // no holiday
    }

    $stmt->close();
    $conn2->close();
}
?>
