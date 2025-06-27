<?php
include '../config/connection.php';
session_start();

$group_name = $_POST['group_name'];
$user_ids = json_decode($_POST['user_ids']); // Expecting a JSON array of user IDs
$creator_id = $_SESSION['employeeid'];

if (empty($group_name) || empty($user_ids)) {
    http_response_code(400);
    echo "Group name and members are required.";
    exit;
}

// Add the creator to the user_ids array if not already present
if (!in_array($creator_id, $user_ids)) {
    $user_ids[] = $creator_id;
}

$conn->begin_transaction();

try {
    // Create the group
    $stmt = $conn->prepare("INSERT INTO chat_groups (group_name, creator_id) VALUES (?, ?)");
    $stmt->bind_param("ss", $group_name, $creator_id);
    $stmt->execute();
    $group_id = $stmt->insert_id;
    $stmt->close();

    // Add members to the group
    $stmt = $conn->prepare("INSERT INTO chat_group_members (group_id, user_id) VALUES (?, ?)");
    foreach ($user_ids as $user_id) {
        $stmt->bind_param("is", $group_id, $user_id);
        $stmt->execute();
    }
    $stmt->close();

    $conn->commit();
    echo "Group created successfully.";

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo "Failed to create group: " . $e->getMessage();
}

$conn->close();
?>