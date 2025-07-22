<?php
include '../config/connection.php';

$request_id = $_POST['request_id'];
$employee_id = $_POST['employee_id'];
$req_coe_type = $_POST['req_coe_type'];

// Header data
$request_reason = $_POST['request_reason'];
$date_needed = $_POST['date_needed'];
$request_format = $_POST['request_format'];
$requested_date = $_POST['requested_date'];
$requested_time = $_POST['requested_time'];
$requested_by = $_POST['requested_by'];
$request_status = $_POST['request_status'];

$req_status = 'REQUESTED';

try {
    $conn->begin_transaction();

    // Insert into request_coe_header
    $stmt1 = $conn->prepare("INSERT INTO request_coe_header 
        (request_id, employee_id, coe_type, request_reason, date_needed, request_format, requested_date, requested_time, requested_by, request_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt1->bind_param("ssssssssss", $request_id, $employee_id, $req_coe_type, $request_reason, $date_needed, $request_format, $requested_date, $requested_time, $requested_by, $request_status);
    $stmt1->execute();

    if ($req_coe_type === 'BENEFIT CLAIM' || $req_coe_type === 'BENEFIT CLAIM WITH COMPENSATION') {
        // Benifit Claim data
        $Claim_Type = $_POST['claim_type'];

        if ($req_coe_type === 'BENEFIT CLAIM WITH COMPENSATION') {
            $compensation_identifier = 1;
        } else {
            $compensation_identifier = 0;
        }

        $stmt2 = $conn->prepare("INSERT INTO request_coe_benefitclaim 
            (request_id, employee_id, claim_type, compensation, compensation_details)
            VALUES (?, ?, ?, ?, '')");
        $stmt2->bind_param("sssi", $request_id, $employee_id, $Claim_Type, $compensation_identifier);
        $stmt2->execute();
    }

    if ($req_coe_type === 'TRAVEL') {
        // Travel data
        $travel_datefrom = $_POST['travel_datefrom'];
        $travel_dateto = $_POST['travel_dateto'];
        $date_return = $_POST['date_return'];
        $travel_type = $_POST['travel_type'];
        $travel_location = $_POST['travel_location'];

        $stmt2 = $conn->prepare("INSERT INTO request_coe_travel 
            (request_id, employee_id, travel_datefrom, travel_dateto, date_return, travel_type, travel_location)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("sssssss", $request_id, $employee_id, $travel_datefrom, $travel_dateto, $date_return, $travel_type, $travel_location);
        $stmt2->execute();
    }

    if ($req_coe_type === 'FINANCIAL') {
        $stmt2 = $conn->prepare("INSERT INTO request_coe_financial 
            (request_id, employee_id, purpose_details)
            VALUES (?, ?, '')");
        $stmt2->bind_param("ss", $request_id, $employee_id);
        $stmt2->execute();
    }

    if ($req_coe_type === 'TRAINING/EDUCATIONAL') {
        $stmt2 = $conn->prepare("INSERT INTO request_coe_training 
            (request_id, employee_id, employee_title, purpose_details)
            VALUES (?, ?, '', '')");
        $stmt2->bind_param("ss", $request_id, $employee_id);
        $stmt2->execute();
    }

    // LOGS
    $action_remarks = 'Request Submitted';
    $stmtLogs = $conn->prepare("INSERT INTO request_coe_logs
        (request_id, employee_id, action_remarks, request_status, action_by, action_date, action_time)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtLogs->bind_param("sssssss", $request_id, $employee_id, $action_remarks, $req_status, $requested_by, $requested_date, $requested_time);
    if (!$stmtLogs->execute()) {
        throw new Exception("Failed to insert logs");
    }

    $conn->commit();
    echo "Success";
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
