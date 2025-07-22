<?php
// chat_module/fetch_groups.php
include '../config/connection.php';
session_start();

if (!isset($_SESSION['employeeid'])) {
    http_response_code(403);
    echo "User not logged in.";
    exit;
}

$user_id = $_SESSION['employeeid'];

// Fetches groups where the current user is a member
$stmt = $conn->prepare("SELECT g.group_id, g.group_name FROM chat_groups g JOIN chat_group_members gm ON g.group_id = gm.group_id WHERE gm.user_id = ? ORDER BY g.group_name");

if (!$stmt) {
    http_response_code(500);
    echo "Prepare failed: " . $conn->error;
    exit;
}

$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p class='text-muted text-center p-3'>No groups found. Click 'Create Group' to start one.</p>";
} else {
    while ($row = $result->fetch_assoc()) {
        $groupId = htmlspecialchars($row['group_id']);
        $groupName = htmlspecialchars($row['group_name']);

        // Output each group as a clickable item
        echo "<div class='group-item d-flex align-items-center p-2 border-bottom' style='cursor: pointer;' data-id='{$groupId}' data-name='{$groupName}'>";
        // You can use a generic group icon
        echo "<img src='assets/imgs/group-default.png' class='profile-pic me-2' alt='Group Icon' style='width:36px; height:36px; border-radius:50%;'>";
        echo "<div><strong>{$groupName}</strong></div>";
        echo "</div>";
    }
}

$stmt->close();
$conn->close();
?>
