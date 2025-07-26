<?php
include '../config/connection.php';
session_start(); // Assuming session is needed for user authentication/authorization

// Check if employeeid is set in session for authorization
if (!isset($_SESSION['employeeid'])) {
    http_response_code(403); // Forbidden
    echo 'Unauthorized access.';
    exit;
}

$message_id = $_POST['id'] ?? '';
$user_id = $_SESSION['employeeid']; // The current logged-in user

if (empty($message_id)) {
    http_response_code(400); // Bad Request
    echo 'Missing message ID.';
    exit;
}

// First, get the attachment path from the database before deleting the message
$stmt_select = $conn->prepare("SELECT attachment_path FROM chat_messages WHERE id = ? AND sender_id = ?");
if (!$stmt_select) {
    http_response_code(500);
    echo 'Prepare failed for select: ' . $conn->error;
    exit;
}
$stmt_select->bind_param("is", $message_id, $user_id);
if (!$stmt_select->execute()) {
    http_response_code(500);
    echo 'Execute failed for select: ' . $stmt_select->error;
    exit;
}

$result = $stmt_select->get_result();
$attachment_path = null;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $attachment_path = $row['attachment_path'];
}
$stmt_select->close();


// Prepare and execute a SQL DELETE statement
// IMPORTANT: This query allows a user to only delete THEIR OWN message.
// If it's an admin feature, remove `AND sender_id = ?` and add admin role checks.
$stmt_delete = $conn->prepare("DELETE FROM chat_messages WHERE id = ? AND sender_id = ?");
if (!$stmt_delete) {
    http_response_code(500);
    echo 'Prepare failed: ' . $conn->error;
    exit;
}

$stmt_delete->bind_param("is", $message_id, $user_id);

if ($stmt_delete->execute()) {
    if ($stmt_delete->affected_rows > 0) {
        if (!empty($attachment_path)) {
            $file_to_delete = '../' . $attachment_path;
            if (file_exists($file_to_delete)) {
                unlink($file_to_delete);
            }
        }
        echo 'Message deleted successfully.';
    } else {
        http_response_code(404); 
        echo 'Message not found or you are not authorized to delete this message.';
    }
} else {
    http_response_code(500); // Internal Server Error
    echo 'Failed to delete message: ' . $stmt_delete->error;
}

$stmt_delete->close();
$conn->close();
?>