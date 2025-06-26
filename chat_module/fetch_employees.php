<?php
include '../config/connection.php';

// Get the search term from the query string
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare the SQL statement with a WHERE clause for searching
if (!empty($searchTerm)) {
    // Add wildcards for a 'LIKE' search
    $searchTermSQL = '%' . $searchTerm . '%';
    $sql = "CALL CHAT_EMPLOYEE_SEARCH(?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $searchTermSQL);
} else {
    // If no search term, fetch all employees
    $sql = "CALL CHAT_EMPLOYEE_LIST()";
    $stmt = $conn->prepare($sql);
}


if (!$stmt) {
    http_response_code(500);
    echo 'Error preparing statement: ' . $conn->error;
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    http_response_code(500);
    echo 'Error loading employees: ' . $conn->error;
    exit;
}

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
        $imgSrc = 'assets/imgs/user_default.png'; // use a valid fallback image path
    }

    echo '
    <div class="employee-item d-flex align-items-center p-2 border-bottom" data-id="' . $employeeid . '">
        <img src="' . $imgSrc . '" class="profile-pic me-2" alt="Profile">
        <div>
            <strong>[' . $employeeid . ']</strong><br>
            ' . $employee_name . '
        </div>
    </div>';
}


$conn->close();

?>