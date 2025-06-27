<?php
include '../config/connection.php';
session_start();

$user_id = isset($_SESSION['employeeid']) ? $_SESSION['employeeid'] : '';

if (!$user_id) {
    http_response_code(400);
    echo 'Missing employee ID.';
    exit;
}

$stmt_employee = $conn->prepare("CALL CHAT_INBOX_MESSAGES(?)");
$stmt_employee->bind_param('s', $user_id);

if (!$stmt_employee->execute()) {
    http_response_code(500);
    echo 'Error executing procedure: ' . $stmt_employee->error;
    exit;
}

$result = $stmt_employee->get_result();

while ($row = $result->fetch_assoc()) {
    $employeeid = htmlspecialchars($row['employeeid']);
    $employee_name = htmlspecialchars($row['Employee_Name']);

    // Picture is a BLOB binary string
    $blob = $row['picture']; // raw binary data

    if (!empty($blob)) {
        // encode as base64, assuming JPEG, adjust MIME if needed
        $base64 = base64_encode($blob);
        $imgSrc = 'data:image/jpeg;base64,' . $base64;
    } else {
        // fallback image or empty src
        $imgSrc = 'default-profile.png';
    }

    echo '
    <div class="employee-item d-flex justify-content-between align-items-center border-bottom p-2" data-id="' . $employeeid . '" data-name="' . $employee_name . '" data-pic="' . $imgSrc . '">
        <div class="d-flex align-items-center">
            <img src="' . $imgSrc . '" class="profile-pic me-2" alt="Profile">
            <div>
                <strong>[' . $employeeid . ']</strong><br>
                ' . $employee_name . '
            </div>
        </div>
        <div class="dropdown">
            <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                &#8942;
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item text-danger employee-delete-btn" href="#" data-id="' . $employeeid . '">Delete</a></li>
                <li><a class="dropdown-item" href="#">View Profile</a></li>
            </ul>
        </div>
    </div>';
}




$stmt_employee->close();
$conn->close();

?>