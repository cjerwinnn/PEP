<?php
include '../config/connection.php';
session_start();

// Define the upload directory
$uploadDirectory = '../attachments/overtime/'; // Ensure this folder exists on the server
$maxFileSize = 10 * 1024 * 1024; // 10MB in bytes

// Ensure that request_id is provided
if (!isset($_POST['overtime_id']) || empty($_POST['overtime_id'])) {
    die("overtime_id is required.");
}

$overtime_id = $_POST['overtime_id']; 

    foreach ($_FILES['files']['name'] as $key => $originalFilename) {
        $fileTmpPath = $_FILES['files']['tmp_name'][$key];
        $fileSize = $_FILES['files']['size'][$key];

        // Check if the file size exceeds the 10MB limit
        if ($fileSize > $maxFileSize) {
            continue;
        }

        // Prepare file details
        $storedFilename = uniqid() . "_" . basename($originalFilename);
        $fileType = $_FILES['files']['type'][$key];
        $fileExtension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $uploadDate = date('Y-m-d');
        $uploadedTime = date('H:i:s');

        // Prepare the file upload path
        $fileUploadPath = $uploadDirectory . $storedFilename;

        // Move the uploaded file to the server folder
        if (!move_uploaded_file($fileTmpPath, $fileUploadPath)) {
            throw new Exception("File upload failed for " . $originalFilename);
        }

        // Assuming you already have a valid database connection ($conn)
        $stmt = $conn2->prepare("INSERT INTO timekeeping_overtime_attachment 
            (overtime_id, original_filename, stored_filename, file_type, file_size, file_extension, upload_date, uploaded_time, uploaded_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn2->error);
        }

        $userId = $_POST['user_id'];
        
        $stmt->bind_param(
            "sssssssss",
            $overtime_id,
            $originalFilename,
            $storedFilename,
            $fileType,
            $fileSize,
            $fileExtension,
            $uploadDate,
            $uploadedTime,
            $userId
        );

        // Execute the insert statement
        if (!$stmt->execute()) {
            throw new Exception("Insert failed: " . $stmt->error);
        }

        // Close the prepared statement
        $stmt->close();
    }
?>
