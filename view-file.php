<?php
// Get and sanitize the file name
$filename = isset($_GET['file']) ? basename($_GET['file']) : '';
$filepath = __DIR__ . '/attachments/' . $filename;

$allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'];

$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

// Validate extension and file existence
if (!$filename || !in_array($ext, $allowedExtensions) || !file_exists($filepath)) {
    http_response_code(404);
    echo "File not found or access denied.";
    exit;
}

// Set content type
$mime = mime_content_type($filepath);
header("Content-Type: $mime");
header("Content-Length: " . filesize($filepath));

// Serve inline for viewable formats
header('Content-Disposition: inline; filename="' . $filename . '"');
readfile($filepath);
exit;
?>
