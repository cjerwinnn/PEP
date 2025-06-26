<?php
include '../config/connection.php';
session_start();

$sender_id = isset($_SESSION['employeeid']) ? $_SESSION['employeeid'] : '';
$receiver_id = isset($_POST['receiver_id']) ? $_POST['receiver_id'] : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

$attachment_path = null;
$attachment_type = null;

if (!$sender_id || !$receiver_id) {
    http_response_code(400);
    echo 'Missing sender or receiver ID.';
    exit;
}

// Check for file upload
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
    $file_tmp_name = $_FILES['attachment']['tmp_name'];
    $file_name = $_FILES['attachment']['name'];
    $file_type = $_FILES['attachment']['type'];
    $file_size = $_FILES['attachment']['size'];

    // Basic validation
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf']; // Add 'application/pdf'
    $max_size = 5 * 1024 * 1024; // 5 MB

    if (!in_array($file_type, $allowed_types)) {
        http_response_code(400);
        echo 'Invalid file type. Only JPG, PNG, GIF, and PDF are allowed.';
        exit;
    }
    if ($file_size > $max_size) {
        http_response_code(400);
        echo 'File size exceeds 5MB limit.';
        exit;
    }

    // Generate a unique filename to prevent conflicts and security issues
    $upload_dir = '../uploads/chat_attachments/'; // Make sure this directory exists and is writable
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $extension = pathinfo($file_name, PATHINFO_EXTENSION);
    $unique_filename = uniqid() . '.' . $extension;
    $destination = $upload_dir . $unique_filename;

    if (move_uploaded_file($file_tmp_name, $destination)) {
        $attachment_path = 'uploads/chat_attachments/' . $unique_filename; // Store relative path
        $attachment_type = $file_type;
    } else {
        http_response_code(500);
        echo 'Failed to upload file.';
        exit;
    }
}

// If no message text and no attachment, prevent sending an empty message
if (empty($message) && empty($attachment_path)) {
    http_response_code(400);
    echo 'Message text or attachment is required.';
    exit;
}

// Insert message into DB
// Modified SQL to include attachment_path and attachment_type
$sql = "INSERT INTO chat_messages (sender_id, receiver_id, message, sent_at, attachment_path, attachment_type) VALUES (?, ?, ?, NOW(), ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo 'Prepare failed: ' . $conn->error;
    exit;
}

// Use 'sssss' for bind_param (sender_id, receiver_id, message, attachment_path, attachment_type)
// All these fields are expected to be strings.
$stmt->bind_param("sssss", $sender_id, $receiver_id, $message, $attachment_path, $attachment_type);

if ($stmt->execute()) {
    echo "Message sent";
} else {
    http_response_code(500);
    echo "Failed to send message: " . $stmt->error;
}

$stmt->close();
$conn->close();
<<<<<<< HEAD
=======
?>
>>>>>>> 06b95ea615e0c6633d114500752a4081b56d7ba6
