<?php
include '../config/connection.php';
session_start();

$sender_id = $_SESSION['employeeid'] ?? '';
$receiver_id = $_GET['receiver_id'] ?? '';
$project_name = 'PEP';

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

// Get messages
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
    '1' => '👍', '2' => '❤️', '3' => '😂', '4' => '😮', '5' => '😢', '6' => '🔥', '7' => '🎉', '8' => '😡',
];

date_default_timezone_set('Asia/Manila');

foreach ($messages as $msg) {
    $isSender = ($msg['sender_id'] === $sender_id);
    $class = $isSender ? 'bg-primary text-white' : 'bg-light text-dark';
    $bubbleAlignment = $isSender ? 'ms-auto' : 'me-auto';
    $imgSrc = $isSender ? $sender_picture : $receiver_picture;

    $sender_name = $msg['sender_name'];
    $receiver_name = $msg['receiver_name'];

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
    if (!$isSender) echo "<img src='{$imgSrc}' class='rounded-circle me-2' width='36' height='36' alt='Profile'>";
    echo "<div class='position-relative p-3 rounded-3 shadow-sm {$class} {$bubbleAlignment}' style='max-width: 75%; min-width: 150px;'>";

    echo "<div class='position-absolute top-0 end-0 mt-1 me-1 d-flex gap-1'>";
    echo "<div class='dropdown'><button class='btn btn-sm btn-light border px-1 py-0' type='button' data-bs-toggle='dropdown'><i class='bi bi-three-dots-vertical'></i></button><ul class='dropdown-menu dropdown-menu-end'>";
    if ($isSender) {
        echo "<li><a class='dropdown-item text-danger message-delete-btn' href='#' data-id='{$msg['id']}'>Delete</a></li>";
    }
    echo "<li><a class='dropdown-item' href='#'>Reply</a></li><li><a class='dropdown-item' href='#'>Forward</a></li></ul></div>";

    if (!$isSender) { // Only show reaction button for received messages
        echo "<button class='btn btn-sm btn-outline-secondary px-1 py-0 reaction-popup-btn' data-id='{$msg['id']}' title='React'><i class='bi bi-emoji-smile'></i></button>";
    }
    echo "</div>";

    if (!empty($attachmentHtml)) echo $attachmentHtml;
    if (!empty($messageText)) echo "<div class='fw-normal pe-4' style='white-space: pre-line;'>{$messageText}</div>";
    echo "<div class='small text-end mt-2 opacity-75'>{$fullDateTime}</div>";

    if (!empty($reactionEmoji)) {
        echo "<span class='reaction-display' data-id='{$msg['id']}' style='position: absolute; bottom: -10px; right: -10px; font-size: 1.2em; background: #fff; border-radius: 50%; padding: 2px 5px; box-shadow: 0 0 6px rgba(0,0,0,0.1);'>{$reactionEmoji}</span>";
    }

    echo "</div>";
    if ($isSender) echo "<img src='{$imgSrc}' class='rounded-circle ms-2' width='36' height='36' alt='Profile'>";
    echo "</div>";
}
?>