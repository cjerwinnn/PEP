<?php
include '../config/connection.php';
session_start();

$uploadDirectory = '../attachments/';
$maxFileSize = 10 * 1024 * 1024;

$request_id = $_POST['request_id'] ?? '';
$user_id = $_POST['user_id'] ?? '';
$checklist_ids = $_POST['checklist_ids'] ?? [];

if (!$request_id || !$user_id || empty($checklist_ids)) {
    die("Missing data.");
}

foreach ($checklist_ids as $key => $checklistId) {
    $hasFile = isset($_FILES['checklist_files']['error'][$key]) && $_FILES['checklist_files']['error'][$key] === UPLOAD_ERR_OK;

    if ($hasFile) {
        $originalFilename = $_FILES['checklist_files']['name'][$key];
        $fileTmpPath = $_FILES['checklist_files']['tmp_name'][$key];
        $fileSize = $_FILES['checklist_files']['size'][$key];
        $fileType = $_FILES['checklist_files']['type'][$key];
        $fileExtension = pathinfo($originalFilename, PATHINFO_EXTENSION);

        if ($fileSize > $maxFileSize) {
            continue;
        }

        $storedFilename = uniqid() . "_" . basename($originalFilename);
        $uploadDate = date('Y-m-d');
        $uploadedTime = date('H:i:s');
        $fileUploadPath = $uploadDirectory . $storedFilename;

        if (!move_uploaded_file($fileTmpPath, $fileUploadPath)) {
            continue;
        }
    } else {
        $originalFilename = 'No Attachment';
        $storedFilename = null;
        $fileType = null;
        $fileSize = 0;
        $fileExtension = null;
        $uploadDate = date('Y-m-d');
        $uploadedTime = date('H:i:s');
    }

    $stmt = $conn->prepare("INSERT INTO request_coe_checklist_attachments 
        (request_id, checklist_id, original_filename, stored_filename, file_type, file_size, file_extension, upload_date, uploaded_time, uploaded_by) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "ssssssssss",
        $request_id,
        $checklistId,
        $originalFilename,
        $storedFilename,
        $fileType,
        $fileSize,
        $fileExtension,
        $uploadDate,
        $uploadedTime,
        $user_id
    );

    $stmt->execute();
    $stmt->close();
}
?>
