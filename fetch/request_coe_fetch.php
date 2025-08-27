<?php

include '../config/connection.php'; // Ensure connection to the database

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check if 'br_number' is provided
    if (!empty($_POST['request_id'])) {
        $request_id = $_POST['request_id'];

        // Prepare the statement
        $stmt = $conn->prepare("CALL COE_HEADER_FETCH(?)");
        if (!$stmt) {
            http_response_code(500);
            echo 'Error: Failed to prepare statement - ' . $conn->error;
            exit;
        }

        // Bind the parameter
        $stmt->bind_param('s', $request_id);

        // Execute the statement
        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();

                // Store result in session
                $_SESSION['request_id'] = $row['request_id'];
                $_SESSION['employee_id'] = $row['employee_id'];
                $_SESSION['coe_type'] = $row['coe_type'];
                $_SESSION['request_reason'] = $row['request_reason'];
                $_SESSION['date_needed'] = $row['date_needed'];
                $_SESSION['request_format'] = $row['request_format'];
                $_SESSION['requested_date'] = $row['requested_date'];
                $_SESSION['requested_time'] = $row['requested_time'];
                $_SESSION['requested_by'] = $row['requested_by'];
                $_SESSION['request_status'] = $row['request_status'];
                $coe_type = $row['coe_type'];
            } else {
                http_response_code(404);
                echo 'Error: No records found.';
            }

            $result->free_result();
        } else {
            http_response_code(500);
            echo 'Error: Failed to execute statement - ' . $stmt->error;
        }

        $stmt->close();


        // ATTACHMENTS

        $stmtLogs = $conn->prepare("CALL COE_SUPPORTINGDOCUMENTS_FETCH(?)");
        if (!$stmtLogs) {
            die("Prepare failed: " . $conn->error);
        }

        $stmtLogs->bind_param('s', $request_id);

        if ($stmtLogs->execute()) {
            $resultAttachments = $stmtLogs->get_result();

            // Initialize an array to hold item data
            $AttachmentsData = [];
            if ($resultAttachments->num_rows > 0) {
                while ($rowItem = $resultAttachments->fetch_assoc()) {
                    $AttachmentsData[] = $rowItem;
                }
            }

            // Store the items data in session
            $_SESSION['AttachmentsData'] = $AttachmentsData;
        } else {
            die("Execute failed: " . $stmtLogs->error);
        }

        $stmtLogs->close();


        // CHECKLIST ATTACHMENTS

        $stmtLogs = $conn->prepare("CALL COE_CHECKLISTATTACHMENTS_FETCH(?,?)");
        if (!$stmtLogs) {
            die("Prepare failed: " . $conn->error);
        }

        $stmtLogs->bind_param('ss', $request_id, $coe_type);

        if ($stmtLogs->execute()) {
            $result_ckAttachments = $stmtLogs->get_result();

            // Initialize an array to hold item data
            $checkListData = [];
            if ($result_ckAttachments->num_rows > 0) {
                while ($rowItem = $result_ckAttachments->fetch_assoc()) {
                    $checkListData[] = $rowItem;
                }
            }

            // Store the items data in session
            $_SESSION['checkListData'] = $checkListData;
        } else {
            die("Execute failed: " . $stmtLogs->error);
        }

        $stmtLogs->close();


        //TRAVEL
        if ($coe_type === 'TRAVEL') {
            // Prepare the statement
            $stmt_travel = $conn->prepare("CALL COE_TRAVEL_FETCH(?)");
            if (!$stmt_travel) {
                http_response_code(500);
                echo 'Error: Failed to prepare statement - ' . $conn->error;
                exit;
            }

            // Bind the parameter
            $stmt_travel->bind_param('s', $request_id);

            // Execute the statement
            if ($stmt_travel->execute()) {
                $result_travel = $stmt_travel->get_result();

                if ($result_travel && $result_travel->num_rows > 0) {
                    $row_travel = $result_travel->fetch_assoc();

                    // Store result in session
                    $_SESSION['request_id'] = $row_travel['request_id'];
                    $_SESSION['employee_id'] = $row_travel['employee_id'];
                    $_SESSION['travel_datefrom'] = $row_travel['travel_datefrom'];
                    $_SESSION['travel_dateto'] = $row_travel['travel_dateto'];
                    $_SESSION['date_return'] = $row_travel['date_return'];
                    $_SESSION['travel_type'] = $row_travel['travel_type'];
                    $_SESSION['travel_location'] = $row_travel['travel_location'];
                } else {
                    http_response_code(404);
                    echo 'Error: No records found.';
                }

                $result_travel->free_result();
            } else {
                http_response_code(500);
                echo 'Error: Failed to execute statement - ' . $stmt_travel->error;
            }

            $stmt_travel->close();
        }

        //BENEFIT CLAIM
        if ($coe_type === 'BENEFIT CLAIM' || $coe_type === 'BENEFIT CLAIM WITH COMPENSATION') {
            // Prepare the statement
            $stmt_benefit = $conn->prepare("CALL COE_BENEFITCLAIM_FETCH(?)");
            if (!$stmt_benefit) {
                http_response_code(500);
                echo 'Error: Failed to prepare statement - ' . $conn->error;
                exit;
            }

            // Bind the parameter
            $stmt_benefit->bind_param('s', $request_id);

            // Execute the statement
            if ($stmt_benefit->execute()) {
                $result_benefit = $stmt_benefit->get_result();

                if ($result_benefit && $result_benefit->num_rows > 0) {
                    $row_benefit = $result_benefit->fetch_assoc();

                    // Store result in session
                    $_SESSION['request_id'] = $row_benefit['request_id'];
                    $_SESSION['employee_id'] = $row_benefit['employee_id'];
                    $_SESSION['claim_type'] = $row_benefit['claim_type'];
                    $_SESSION['compensation'] = $row_benefit['compensation'];
                    $_SESSION['compensation_details'] = $row_benefit['compensation_details'];
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
        }

        //FINANCIAL
        if ($coe_type === 'FINANCIAL') {
            // Prepare the statement
            $stmt_financial = $conn->prepare("CALL COE_FINANCIAL_FETCH(?)");
            if (!$stmt_financial) {
                http_response_code(500);
                echo 'Error: Failed to prepare statement - ' . $conn->error;
                exit;
            }

            // Bind the parameter
            $stmt_financial->bind_param('s', $request_id);

            // Execute the statement
            if ($stmt_financial->execute()) {
                $result_financial = $stmt_financial->get_result();

                if ($result_financial && $result_financial->num_rows > 0) {
                    $row_financial = $result_financial->fetch_assoc();

                    // Store result in session
                    $_SESSION['request_id'] = $row_financial['request_id'];
                    $_SESSION['employee_id'] = $row_financial['employee_id'];
                    $_SESSION['purpose_details'] = $row_financial['purpose_details'];
                } else {
                    http_response_code(404);
                    echo 'Error: No records found.';
                }

                $result_financial->free_result();
            } else {
                http_response_code(500);
                echo 'Error: Failed to execute statement - ' . $stmt_financial->error;
            }

            $stmt_financial->close();
        }

        //TRAINING
        if ($coe_type === 'TRAINING/EDUCATIONAL') {
            // Prepare the statement
            $stmt_training = $conn->prepare("CALL COE_TRAINING_FETCH(?)");
            if (!$stmt_training) {
                http_response_code(500);
                echo 'Error: Failed to prepare statement - ' . $conn->error;
                exit;
            }

            // Bind the parameter
            $stmt_training->bind_param('s', $request_id);

            // Execute the statement
            if ($stmt_training->execute()) {
                $result_training = $stmt_training->get_result();

                if ($result_training && $result_training->num_rows > 0) {
                    $row_training = $result_training->fetch_assoc();

                    // Store result in session
                    $_SESSION['request_id'] = $row_training['request_id'];
                    $_SESSION['employee_id'] = $row_training['employee_id'];
                    $_SESSION['employee_title'] = $row_training['employee_title'];
                    $_SESSION['purpose_details'] = $row_training['purpose_details'];
                } else {
                    http_response_code(404);
                    echo 'Error: No records found.';
                }

                $result_training->free_result();
            } else {
                http_response_code(500);
                echo 'Error: Failed to execute statement - ' . $stmt_training->error;
            }

            $stmt_training->close();
        }


        // ATTACHMENTS

        $stmtLogs = $conn->prepare("CALL COE_LOGS(?)");
        if (!$stmtLogs) {
            die("Prepare failed: " . $conn->error);
        }

        $stmtLogs->bind_param('s', $request_id);

        if ($stmtLogs->execute()) {
            $resultAttachments = $stmtLogs->get_result();

            // Initialize an array to hold item data
            $LogsData = [];
            if ($resultAttachments->num_rows > 0) {
                while ($rowItem = $resultAttachments->fetch_assoc()) {
                    $LogsData[] = $rowItem;
                }
            }

            // Store the items data in session
            $_SESSION['LogsData'] = $LogsData;
        } else {
            die("Execute failed: " . $stmtLogs->error);
        }

        $stmtLogs->close();
    } else {
        http_response_code(400);
        echo 'Error: Missing parameter.';
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo 'Error: Invalid request method. Only POST is allowed.';
}

// Close the DB connection
$conn->close();
