<?php
include '../config/connection.php';
session_start();

// Add this security check at the top
if (!isset($_SESSION['employeeid'])) {
    http_response_code(403); // Forbidden
    echo 'Unauthorized. Please log in.';
    exit; // Stop script execution
}

if (isset($_SESSION['employeeid'])) {
    $user_id = $_SESSION['employeeid'];
    $update_sql = "UPDATE hris_live_db.system_users SET last_activity = NOW() WHERE user = ?";
    $update_stmt = $conn->prepare($update_sql);
    if ($update_stmt) {
        $update_stmt->bind_param('s', $user_id);
        $update_stmt->execute();
        $update_stmt->close();
    }
}

$sender_id = $_SESSION['employeeid'];
$receiver_id = $_GET['receiver_id'] ?? '';
$project_name = 'PEP';

$update_stmt = $conn2->prepare("UPDATE system_users SET last_activity = NOW() WHERE user = ?");
$update_stmt->bind_param('s', $sender_id);
$update_stmt->execute();
$update_stmt->close();
$conn2->next_result(); // Clear the result set

if (!$sender_id || !$receiver_id) {
    http_response_code(400);
    echo 'Missing parameters.';
    exit;
}

// Get sender picture
$stmt_sender = $conn->prepare("CALL CHAT_SENDER_PICTURE(?)");
$stmt_sender->bind_param("s", $sender_id);
$stmt_sender->execute();
$result_sender = $stmt_sender->get_result();
$sender_pic_row = $result_sender->fetch_assoc();
$sender_picture = (!empty($sender_pic_row) && !empty($sender_pic_row['picture'])) ? 'data:image/jpeg;base64,' . base64_encode($sender_pic_row['picture']) : 'assets/imgs/user_default.png';
$stmt_sender->close();
$conn->next_result();

// Get receiver picture
$stmt_receiver = $conn->prepare("CALL CHAT_RECEIVER_PICTURE(?)");
$stmt_receiver->bind_param("s", $receiver_id);
$stmt_receiver->execute();
$result_receiver = $stmt_receiver->get_result();
$receiver_pic_row = $result_receiver->fetch_assoc();
$receiver_picture = (!empty($receiver_pic_row) && !empty($receiver_pic_row['picture'])) ? 'data:image/jpeg;base64,' . base64_encode($receiver_pic_row['picture']) : 'assets/imgs/user_default.png';
$stmt_receiver->close();
$conn->next_result();

// Get messages using the updated stored procedure
$stmt = $conn->prepare("CALL CHAT_MESSAGES_DATA(?, ?)");
$stmt->bind_param("ss", $sender_id, $receiver_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();
$conn->close();

$reactionMap = [
    '1' => '👍',
    '2' => '❤️',
    '3' => '😂',
    '4' => '😮',
    '5' => '😢',
    '6' => '🔥',
    '7' => '🎉',
    '8' => '😡',
];

date_default_timezone_set('Asia/Manila');

foreach ($messages as $msg) {
    $isSender = ($msg['sender_id'] === $sender_id);
    $class = $isSender ? 'bg-primary text-white' : 'bg-light text-dark';
    $bubbleAlignment = $isSender ? 'ms-auto' : 'me-auto';
    $imgSrc = $isSender ? $sender_picture : $receiver_picture;

    $senderName = 'You'; // Assuming you don't have the sender's full name here.
    $receiverName = htmlspecialchars($receiver_pic_row['Employee_Name'] ?? 'Receiver');

    $messageText = htmlspecialchars($msg['message']);
    $timestamp = strtotime($msg['sent_at']);
    $fullDateTime = date('F j, Y H:i', $timestamp);
    $rawReaction = $msg['reaction'] ?? '';
    $reactionEmoji = $reactionMap[$rawReaction] ?? $rawReaction;

    $attachmentPath = htmlspecialchars($msg['attachment_path'] ?? '');
    $attachmentType = htmlspecialchars($msg['attachment_type'] ?? '');
    $attachmentHtml = '';

    if (!empty($attachmentPath)) {
        $fullAttachmentUrl = '/' . $project_name . '/' . $attachmentPath;
        $filename = basename($attachmentPath);

        if (str_starts_with($attachmentType, 'image/')) {
            $attachmentHtml = "<div class='message-attachment' data-src='{$fullAttachmentUrl}' data-type='{$attachmentType}' data-filename='{$filename}'><img src='{$fullAttachmentUrl}' alt='Attached Image'></div>";
        } else {
            $iconSrc = $attachmentType === 'application/pdf' ? 'assets/icons/pdf-icon.png' : 'assets/icons/file-icon.png';
            $iconStyle = $attachmentType === 'application/pdf' ? 'style="width:30px; height:auto;"' : '';
            $attachmentHtml = "<div class='message-attachment' data-src='{$fullAttachmentUrl}' data-type='{$attachmentType}' data-filename='{$filename}'><a href='{$fullAttachmentUrl}' target='_blank' style='color:inherit; text-decoration:none;'><img src='{$iconSrc}' {$iconStyle} alt='File Icon' class='attachment-icon'> {$filename}</a></div>";
        }
    }

    echo "<div class='d-flex w-100 align-items-end mb-3' data-message-id='{$msg['id']}'>";
    if (!$isSender) {
        echo "<img src='{$imgSrc}' class='profile-pic rounded-circle me-2' width='36' height='36' alt='Profile' title='{$receiverName}'>";
    }

    echo "<div class='position-relative p-3 rounded-3 shadow-sm {$class} {$bubbleAlignment}' style='max-width: 75%; min-width: 150px;'>";

    echo "<div class='position-absolute top-0 end-0 mt-1 me-1 d-flex gap-1'>";
    echo "<button class='btn btn-sm btn-light border px-1 py-0 message-options-btn' data-id='{$msg['id']}' data-is-sender='" . ($isSender ? '1' : '0') . "'><i class='bi bi-three-dots-vertical'></i></button>";
    if (!$isSender) { // Only show reaction button for received messages
        echo "<button class='btn btn-sm btn-outline-secondary px-1 py-0 reaction-popup-btn' data-id='{$msg['id']}' title='React'><i class='bi bi-emoji-smile'></i></button>";
    }
    echo "</div>";

    // --- NEW: Render the reply snippet ---
    $repliedMessage = htmlspecialchars($msg['replied_message'] ?? '');
    $repliedSenderName = htmlspecialchars($msg['replied_sender_name'] ?? 'A message');

    if (!empty($repliedMessage)) {
        echo "<div class='reply-snippet' style='background-color: rgba(0,0,0,0.08); padding: 8px 10px; border-radius: 6px; margin-bottom: 8px; border-left: 3px solid #0d6efd;'>";
        echo "<strong class='small'>Replying to {$repliedSenderName}</strong>";
        echo "<div class='small text-muted' style='white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>{$repliedMessage}</div>";
        echo "</div>";
    }
    // --- END of new code ---

    if (!empty($attachmentHtml)) echo $attachmentHtml;
    if (!empty($messageText)) echo "<div class='fw-normal pe-4' style='white-space: pre-line;'>{$messageText}</div>";
    echo "<div class='small text-end mt-2 opacity-75'>{$fullDateTime}</div>";

    if (!empty($reactionEmoji)) {
        echo "<span class='reaction-display' data-id='{$msg['id']}' style='position: absolute; bottom: -10px; right: -10px; font-size: 1.2em; background: #fff; border-radius: 50%; padding: 2px 5px; box-shadow: 0 0 6px rgba(0,0,0,0.1);'>{$reactionEmoji}</span>";
    }

    echo "</div>";
    if ($isSender) {
        echo "<img src='{$imgSrc}' class='profile-pic rounded-circle ms-2' width='36' height='36' alt='Profile' title='{$senderName}'>";
    }
    echo "</div>";
}
