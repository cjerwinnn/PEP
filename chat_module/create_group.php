<?php
include '../config/connection.php';
session_start();

if (!isset($_SESSION['employeeid'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated.']);
    exit;
}

$group_name = trim($_POST['group_name'] ?? '');
$user_ids_json = $_POST['user_ids'] ?? '[]';
$user_ids = json_decode($user_ids_json);
$creator_id = $_SESSION['employeeid'];

if (empty($group_name) || empty($user_ids) || !is_array($user_ids)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Group name and at least one member are required.']);
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
    if (!$stmt) {
        throw new Exception("Failed to prepare statement for group creation: " . $conn->error);
    }
    $stmt->bind_param("ss", $group_name, $creator_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to create group: " . $stmt->error);
    }
    $group_id = $stmt->insert_id;
    $stmt->close();

    // Add members to the group
    $stmt_members = $conn->prepare("INSERT INTO chat_group_members (group_id, user_id) VALUES (?, ?)");
    if (!$stmt_members) {
        throw new Exception("Failed to prepare statement for group members: " . $conn->error);
    }
    foreach ($user_ids as $user_id) {
        $stmt_members->bind_param("is", $group_id, $user_id);
        if (!$stmt_members->execute()) {
            throw new Exception("Failed to add member to group: " . $stmt_members->error);
        }
    }
    $stmt_members->close();

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Group created successfully.']);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>