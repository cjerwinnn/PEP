<?php
include '../config/connection.php';
session_start();

$sender_id = $_SESSION['employeeid'] ?? '';
$receiver_id = $_GET['receiver_id'] ?? '';

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
$sender_picture = !empty($sender_pic_row['picture']) ? 'data:image/jpeg;base64,' . base64_encode($sender_pic_row['picture']) : 'default-profile.png';
$stmt_sender->close();
$conn->next_result();

// Get receiver picture
$stmt_receiver = $conn->prepare("CALL CHAT_RECEIVER_PICTURE(?)");
$stmt_receiver->bind_param("s", $receiver_id);
$stmt_receiver->execute();
$result_receiver = $stmt_receiver->get_result();
$receiver_pic_row = $result_receiver->fetch_assoc();
$receiver_picture = !empty($receiver_pic_row['picture']) ? 'data:image/jpeg;base64,' . base64_encode($receiver_pic_row['picture']) : 'default-profile.png';
$stmt_receiver->close();
$conn->next_result();

// Get messages
// IMPORTANT: Ensure your CHAT_MESSAGES_DATA stored procedure (or your direct query)
// now includes 'attachment_path' and 'attachment_type' in its SELECT statement.
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

    $messageText = htmlspecialchars($msg['message']);
    $timestamp = strtotime($msg['sent_at']);
    if (!$timestamp) continue;

    $fullDateTime = date('F j, Y H:i', $timestamp);
    $rawReaction = $msg['reaction'] ?? '';
    $reactionEmoji = isset($reactionMap[$rawReaction]) ? $reactionMap[$rawReaction] : $rawReaction;

    $attachmentPath = htmlspecialchars($msg['attachment_path'] ?? '');
    $attachmentType = htmlspecialchars($msg['attachment_type'] ?? '');
    $attachmentHtml = '';

    if (!empty($attachmentPath)) {
        // Adjust path for client-side access. Assuming 'uploads/' is directly accessible from web root.
        $fullAttachmentUrl = '/' .$project_name . '/' . $attachmentPath; // <-- This is the key change
        $filename = basename($attachmentPath); // Get just the filename

        if (str_starts_with($attachmentType, 'image/')) {
            $attachmentHtml = "
                <div class='message-attachment' data-src='{$fullAttachmentUrl}' data-type='{$attachmentType}' data-filename='{$filename}'>
                    <img src='{$fullAttachmentUrl}' alt='Attached Image'>
                </div>";
        } elseif ($attachmentType === 'application/pdf') {
            $attachmentHtml = "
                <div class='message-attachment' data-src='{$fullAttachmentUrl}' data-type='{$attachmentType}' data-filename='{$filename}'>
                    <a href='{$fullAttachmentUrl}' target='_blank' style='color:inherit; text-decoration:none;'>
                        <img src='assets/icons/pdf-icon.png' alt='PDF' class='attachment-icon'> {$filename}
                    </a>
                </div>";
        } else {
            // For other file types, provide a generic file icon and a download link
            $attachmentHtml = "
                <div class='message-attachment' data-src='{$fullAttachmentUrl}' data-type='{$attachmentType}' data-filename='{$filename}'>
                    <a href='{$fullAttachmentUrl}' target='_blank' style='color:inherit; text-decoration:none;'>
                        <img src='assets/icons/file-icon.png' alt='File' class='attachment-icon'> {$filename}
                    </a>
                </div>";
        }
    }


    echo "<div class='d-flex w-100 align-items-end mb-3' data-message-id='{$msg['id']}'>";

    // Receiver image
    if (!$isSender) {
        echo "<img src='{$imgSrc}' class='rounded-circle me-2' width='36' height='36' alt='Profile'>";
    }

    // Message bubble with menu and reaction button inside
    echo "<div class='position-relative p-3 rounded-3 ms-3 shadow-sm {$class} {$bubbleAlignment}' style='max-width: 75%; min-width: 150px;'>";

    // Top-right controls
    echo "<div class='position-absolute top-0 end-0 mt-1 me-1 d-flex gap-1'>";
    echo "<div class='dropdown'>
                    <button class='btn btn-sm btn-light border px-1 py-0' type='button' data-bs-toggle='dropdown'>
                        <i class='bi bi-three-dots-vertical'></i>
                    </button>
                    <ul class='dropdown-menu dropdown-menu-end'>
                        <li><a class='dropdown-item' href='#'>Reply</a></li>
                        <li><a class='dropdown-item' href='#'>Forward</a></li>
                        <li><a class='dropdown-item text-danger message-delete-btn' href='#' data-id='{$msg['id']}'>Delete</a></li>
                    </ul>
                  </div>";
    echo "<button class='btn btn-sm btn-outline-secondary px-1 py-0 reaction-popup-btn' data-id='{$msg['id']}' title='React'>
                    <i class='bi bi-emoji-smile'></i>
                  </button>";
    echo "</div>";

    // Display attachment here
    if (!empty($attachmentHtml)) {
        echo $attachmentHtml;
    }

    // Only display message text if it's not empty
    if (!empty($messageText)) {
        echo "<div class='fw-normal pe-4' style='white-space: pre-line;'>{$messageText}</div>";
    }


    // Timestamp
    echo "<div class='small text-end mt-2 opacity-75'>{$fullDateTime}</div>";

    // Reaction emoji overlay
    if (!empty($reactionEmoji)) {
        echo "<span class='reaction-display' data-id='{$msg['id']}'
                     style='position: absolute; bottom: -10px; right: -10px; font-size: 1.2em; background: #fff; border-radius: 50%; padding: 2px 5px; box-shadow: 0 0 6px rgba(0,0,0,0.1);'>
                  {$reactionEmoji}
                  </span>";
    }

    echo "</div>"; // end message bubble

    // Sender image
    if ($isSender) {
        echo "<img src='{$imgSrc}' class='rounded-circle ms-2' width='36' height='36' alt='Profile' title='{$isSender}'>";
    }

    echo "</div>"; // end row
}
