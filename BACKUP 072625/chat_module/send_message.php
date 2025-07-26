<?php
include '../config/connection.php';
session_start();

$sender_id = $_SESSION['employeeid'] ?? '';
$receiver_id = $_POST['receiver_id'] ?? null;
$group_id = $_POST['group_id'] ?? null;
$message = trim($_POST['message'] ?? '');
$reply_to_message_id = $_POST['reply_to_message_id'] ?? null;

if (empty($sender_id) || (empty($receiver_id) && empty($group_id))) {
    http_response_code(400);
    echo 'Missing sender ID, or a receiver/group ID.';
    exit;
}

$attachment_path = null;
$attachment_type = null;

// Attachment handling
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
    $file_tmp_name = $_FILES['attachment']['tmp_name'];
    $file_name = $_FILES['attachment']['name'];
    $file_type = $_FILES['attachment']['type'];
    
    // Define allowed types and size
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    $max_size = 10 * 1024 * 1024; // 10 MB

    if (!in_array($file_type, $allowed_types)) {
        http_response_code(400);
        echo 'Invalid file type. Only JPG, PNG, GIF, and PDF are allowed.';
        exit;
    }
    if ($_FILES['attachment']['size'] > $max_size) {
        http_response_code(400);
        echo 'File size exceeds 10MB limit.';
        exit;
    }

    $upload_dir = '../uploads/chat_attachments/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $extension = pathinfo($file_name, PATHINFO_EXTENSION);
    $unique_filename = uniqid() . '.' . $extension;
    $destination = $upload_dir . $unique_filename;

    if (move_uploaded_file($file_tmp_name, $destination)) {
        $attachment_path = 'uploads/chat_attachments/' . $unique_filename;
        $attachment_type = $file_type;
    } else {
        http_response_code(500);
        echo 'Failed to move uploaded file.';
        exit;
    }
}

if (empty($message) && empty($attachment_path)) {
    http_response_code(400);
    echo 'Cannot send an empty message.';
    exit;
}

if ($group_id) {
    $sql = "INSERT INTO chat_messages (sender_id, group_id, message, sent_at, attachment_path, attachment_type, reply_to_message_id) VALUES (?, ?, ?, NOW(), ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisssi", $sender_id, $group_id, $message, $attachment_path, $attachment_type, $reply_to_message_id);
} else {
    $sql = "INSERT INTO chat_messages (sender_id, receiver_id, message, sent_at, attachment_path, attachment_type, reply_to_message_id) VALUES (?, ?, ?, NOW(), ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $sender_id, $receiver_id, $message, $attachment_path, $attachment_type, $reply_to_message_id);
}

if ($stmt && $stmt->execute()) {
    echo "Message sent";
} else {
    http_response_code(500);
    echo "Database error: " . ($stmt ? $stmt->error : $conn->error);
}

if ($stmt) {
    $stmt->close();
}
$conn->close();
?>