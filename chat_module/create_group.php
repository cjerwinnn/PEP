<?php
session_start();
include '../config/connection.php';

// Set header to application/json for consistent responses
header('Content-Type: application/json');

// Check if the user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated. Please log in.']);
    exit;
}

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// Get and sanitize POST data
$group_name = trim($_POST['group_name'] ?? '');
$group_members = $_POST['group_members'] ?? [];
$created_by = $_SESSION['user_id'];

// --- Validation ---
if (empty($group_name)) {
    echo json_encode(['status' => 'error', 'message' => 'Group name cannot be empty.']);
    exit;
}

if (empty($group_members) || !is_array($group_members)) {
    echo json_encode(['status' => 'error', 'message' => 'You must select at least one member for the group.']);
    exit;
}

// Add the group creator to the members list if not already present
if (!in_array($created_by, $group_members)) {
    $group_members[] = $created_by;
}
// Ensure all member IDs are unique
$group_members = array_unique($group_members);

// --- Database Operations with Transaction ---
$conn->begin_transaction();

try {
    // 1. Create the Group
    $group_id = "GROUP_" . uniqid() . rand(1000, 9999);
    $stmt_group = $conn->prepare("INSERT INTO chat_groups (group_id, group_name, created_by) VALUES (?, ?, ?)");
    if (!$stmt_group) {
        throw new Exception('Failed to prepare statement for group creation.');
    }
    $stmt_group->bind_param("sss", $group_id, $group_name, $created_by);
    if (!$stmt_group->execute()) {
        throw new Exception('Failed to execute statement for group creation.');
    }
    $stmt_group->close();

    // 2. Add Members to the Group
    $stmt_member = $conn->prepare("INSERT INTO chat_group_members (group_id, user_id) VALUES (?, ?)");
     if (!$stmt_member) {
        throw new Exception('Failed to prepare statement for adding members.');
    }
    foreach ($group_members as $member_id) {
        $clean_member_id = trim($member_id);
        if (!empty($clean_member_id)) {
            $stmt_member->bind_param("ss", $group_id, $clean_member_id);
            if (!$stmt_member->execute()) {
                // You could log this error or decide if it should halt the whole process
            }
        }
    }
    $stmt_member->close();

    // If all went well, commit the transaction
    $conn->commit();

    echo json_encode([
        'status' => 'success', 
        'message' => 'Group created successfully!',
        'group_id' => $group_id,
        'group_name' => $group_name
    ]);

} catch (Exception $e) {
    // If any step fails, roll back the transaction
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>
