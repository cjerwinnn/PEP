<?php
include 'config/connection.php';

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'];

    $stmt_employee = $conn->prepare("CALL COE_EMPLOYEE_HEADER(?)");
    if (!$stmt_employee) {
        http_response_code(500);
        echo 'Error: Failed to prepare statement - ' . $conn->error;
        exit;
    }

    $stmt_employee->bind_param('s', $employee_id);

    if ($stmt_employee->execute()) {
        $result_employee = $stmt_employee->get_result();

        if ($result_employee && $result_employee->num_rows > 0) {
            $row_employee = $result_employee->fetch_assoc();

            // Save data into session
            $_SESSION['employee_id']   = $row_employee['employeeid'];
            $_SESSION['lastname']   = $row_employee['lastname'];
            $_SESSION['firstname']  = $row_employee['firstname'];
            $_SESSION['middlename'] = $row_employee['middlename'];
            $_SESSION['suffix']     = $row_employee['suffix'];
            $_SESSION['department'] = $row_employee['department'];
            $_SESSION['area']       = $row_employee['area'];
            $_SESSION['position']   = $row_employee['position'];
        } else {
            http_response_code(404);
            echo 'Error: No records found.';
        }

        $result_employee->free();

        // Clear additional result sets from stored procedure
        while ($conn->more_results() && $conn->next_result()) {
            $conn->use_result();
        }
    } else {
        http_response_code(500);
        echo 'Error: Failed to execute statement - ' . $stmt_employee->error;
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo 'Error: Invalid request method. Only POST is allowed.';
}

$stmt_employee->close();
