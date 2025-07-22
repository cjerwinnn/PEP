<?php

include '../../config/connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate input
    if (!empty($_POST['request_id'])) {
        $request_id = $_POST['request_id'];

        // Prepare the stored procedure
        $stmt_benefit = $conn->prepare("CALL COE_BENEFITCLAIM_FETCH(?)");

        if (!$stmt_benefit) {
            http_response_code(500);
            echo 'Error: Failed to prepare statement - ' . $conn->error;
            exit;
        }

        $stmt_benefit->bind_param('s', $request_id);

        if ($stmt_benefit->execute()) {
            $result_benefit = $stmt_benefit->get_result();

            if ($result_benefit && $result_benefit->num_rows > 0) {
                $row_benefit = $result_benefit->fetch_assoc();

                // Optionally store in session if needed later
                $_SESSION['request_id'] = $row_benefit['request_id'];
                $_SESSION['employee_id'] = $row_benefit['employee_id'];
                $_SESSION['claim_type'] = $row_benefit['claim_type'];
                $_SESSION['compensation'] = $row_benefit['compensation'];
                $_SESSION['compensation_details'] = $row_benefit['compensation_details'];

                // ✅ Send only the compensation details back
                echo $row_benefit['compensation_details'];
            } else {
                http_response_code(404);
                echo 'Error: No records found.';
            }

            $result_benefit->free_result();
        } else {
            http_response_code(500);
            echo 'Error: Failed to execute statement - ' . $stmt_benefit->error;
        }

        $stmt_benefit->close();
    } else {
        http_response_code(400);
        echo 'Error: Missing request_id.';
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo 'Error: Invalid request method. Only POST is allowed.';
}

$conn->close();
