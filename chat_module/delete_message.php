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

// Prepare and execute a SQL DELETE statement
// IMPORTANT: This query allows a user to only delete THEIR OWN message.
// If it's an admin feature, remove `AND sender_id = ?` and add admin role checks.
$stmt = $conn->prepare("DELETE FROM chat_messages WHERE id = ? AND sender_id = ?");
if (!$stmt) {
    http_response_code(500);
    echo 'Prepare failed: ' . $conn->error;
    exit;
}

$stmt->bind_param("is", $message_id, $user_id); // 'i' for int (message_id), 's' for string (user_id)

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo 'Message deleted successfully.';
    } else {
        // Message not found, or user is not the sender
        http_response_code(404); // Not Found (if ID doesn't exist) or Forbidden (if user not sender)
        echo 'Message not found or you are not authorized to delete this message.';
    }
} else {
    http_response_code(500); // Internal Server Error
    echo 'Failed to delete message: ' . $stmt->error;
}

$stmt->close();
$conn->close();
?>