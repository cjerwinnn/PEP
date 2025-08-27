<?php
include '../config/connection.php'; // Ensure DB connection
session_start();

// Sanitize input
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username && $password) {
    $stmt = $conn2->prepare("SELECT * FROM system_users WHERE user = ? LIMIT 1");

    if (!$stmt) {
        http_response_code(500);
        echo 'Error: Failed to prepare user query - ' . $conn2->error;
        exit;
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Secure password check
        if ($password === $user['pass']) {

            // Optional: regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            $stmt_emp = $conn->prepare("CALL SYSTEM_EMPLOYEE_DATA(?)");

            if (!$stmt_emp) {
                http_response_code(500);
                echo 'Error: Failed to prepare employee data - ' . $conn->error;
                $stmt->close();
                exit;
            }

            $stmt_emp->bind_param('s', $username);

            if ($stmt_emp->execute()) {
                $result_emp = $stmt_emp->get_result();

                if ($result_emp && $result_emp->num_rows > 0) {
                    $row_emp = $result_emp->fetch_assoc();

                    $_SESSION['picture'] = base64_encode($row_emp['picture']);
                    $_SESSION['lastname']   = $row_emp['lastname'];
                    $_SESSION['firstname']  = $row_emp['firstname'];
                    $_SESSION['middlename'] = $row_emp['middlename'];
                    $_SESSION['suffix']     = $row_emp['suffix'];
                    $_SESSION['department'] = $row_emp['department'];
                    $_SESSION['area']       = $row_emp['area'];
                    $_SESSION['position']   = $row_emp['position'];
                    $_SESSION['email']      = $row_emp['email'];

                    $result_emp->free_result();
                } else {
                    http_response_code(404);
                    echo 'Error: No employee records found.';
                    $stmt_emp->close();
                    $stmt->close();
                    exit;
                }

                $stmt_emp->close();
            } else {
                http_response_code(500);
                echo 'Error: Failed to execute employee data query - ' . $stmt_emp->error;
                $stmt_emp->close();
                $stmt->close();
                exit;
            }

            // Store additional session data
            $_SESSION['employeeid']    = $user['user'];
            $_SESSION['user_fullname'] = $user['user_fullname'];

            $stmt->close();
            header("Location: ../pages/main.php");
            exit;
        } else {
            $stmt->close();
            header("Location: ../index.php?error=wrongpass");
            exit;
        }
    } else {
        $stmt->close();
        header("Location: ../index.php?error=nouser");
        exit;
    }
} else {
    header("Location: ../index.php?error=empty");
    exit;
}

$conn2->close();
