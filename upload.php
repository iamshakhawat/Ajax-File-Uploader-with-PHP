<?php
/**
 * Secure File Upload Handler
 * This script handles file uploads with security measures to prevent malicious file uploads
 */

// Set response header to JSON
header('Content-Type: application/json');

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Configuration
define('UPLOAD_DIR', './u/');  // Upload directory
define('MAX_FILE_SIZE', 104857600);  // 100MB in bytes
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'zip', 'rar', 'mp4', 'mp3', 'xls', 'xlsx', 'ppt', 'pptx', 'svg', 'webp', 'avi', 'mov', 'csv', 'json', 'xml', 'html', 'css', 'js']); // Add more as needed

// Dangerous file extensions that should be blocked
define('BLOCKED_EXTENSIONS', ['php', 'phtml', 'php3', 'php4', 'php5', 'phps', 'pht', 'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar', 'sh', 'asp', 'aspx', 'jsp', 'cgi', 'pl', 'py']);

// MIME type whitelist for additional security
define('ALLOWED_MIME_TYPES', [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/svg+xml',
    'image/webp',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'text/plain',
    'text/csv',
    'application/zip',
    'application/x-rar-compressed',
    'application/x-zip-compressed',
    'video/mp4',
    'video/x-msvideo',
    'video/quicktime',
    'audio/mpeg',
    'audio/wav',
    'application/json',
    'text/xml',
    'application/xml',
    'text/html',
    'text/css',
    'application/javascript',
    'application/octet-stream'
]);

/**
 * Send JSON response
 */
function sendResponse($success, $message, $url = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'url' => $url
    ]);
    exit;
}

/**
 * Create upload directory if it doesn't exist
 */
function createUploadDirectory() {
    if (!file_exists(UPLOAD_DIR)) {
        if (!mkdir(UPLOAD_DIR, 0755, true)) {
            sendResponse(false, 'Failed to create upload directory');
        }
    }
}

/**
 * Generate unique filename to prevent overwriting
 */
function generateUniqueFilename($originalName) {
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $basename = pathinfo($originalName, PATHINFO_FILENAME);
    
    // Sanitize filename - remove special characters
    // $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);
    
    // Generate unique name with timestamp and random string
    $uniqueName = $basename . '_' . time() . '_' . substr(md5(uniqid(rand(), true)), 0, 8) . '.' . $extension;
    
    return $uniqueName;
}

/**
 * Validate file extension
 */
function validateExtension($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    // Block dangerous extensions
    if (in_array($extension, BLOCKED_EXTENSIONS)) {
        return ['valid' => false, 'message' => 'This file type is blocked for security reasons'];
    }
    
    // If you want to only allow specific extensions, uncomment below
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return ['valid' => false, 'message' => 'This file type is not allowed'];
    }
    
    return ['valid' => true, 'message' => ''];
}

/**
 * Validate MIME type
 */
function validateMimeType($fileTmpPath) {
    // Get MIME type using finfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $fileTmpPath);
    finfo_close($finfo);
    
    // Additional check: get MIME type from file command if available
    if (function_exists('mime_content_type')) {
        $mimeType2 = mime_content_type($fileTmpPath);
    }
    
    // For security, you can enforce MIME type whitelist
    // Uncomment below to enable strict MIME checking
    if (!in_array($mimeType, ALLOWED_MIME_TYPES)) {
        return ['valid' => false, 'message' => 'Invalid file type detected'];
    }
    
    return ['valid' => true, 'message' => '', 'mime' => $mimeType];
}

/**
 * Scan file for malicious content
 */
function scanFileContent($filePath, $extension) {
    // Check for PHP code in files that shouldn't have it
    $dangerousPatterns = [
        '/<\?php/i',
        '/<\?=/i',
        '/<script/i',
        '/eval\(/i',
        '/base64_decode/i',
        '/system\(/i',
        '/exec\(/i',
        '/shell_exec/i',
        '/passthru/i',
        '/popen/i'
    ];
    
    // Read first 8KB of file for scanning
    $handle = fopen($filePath, 'r');
    $content = fread($handle, 8192);
    fclose($handle);
    
    // Check for malicious patterns
    foreach ($dangerousPatterns as $pattern) {
        if (preg_match($pattern, $content)) {
            return ['safe' => false, 'message' => 'Potentially malicious content detected'];
        }
    }
    
    return ['safe' => true, 'message' => ''];
}

/**
 * Validate file size
 */
function validateFileSize($fileSize) {
    if ($fileSize > MAX_FILE_SIZE) {
        return ['valid' => false, 'message' => 'File size exceeds maximum allowed size (50MB)'];
    }
    
    if ($fileSize <= 0) {
        return ['valid' => false, 'message' => 'Invalid file size'];
    }
    
    return ['valid' => true, 'message' => ''];
}

/**
 * Main upload handler
 */
function handleFileUpload() {
    // Check if files were uploaded
    if (!isset($_FILES['files']) || empty($_FILES['files']['name'][0])) {
        sendResponse(false, 'No files uploaded');
    }
    
    // Create upload directory
    createUploadDirectory();
    
    // Process the first file (since we're handling one at a time via AJAX)
    $file = [
        'name' => $_FILES['files']['name'][0],
        'type' => $_FILES['files']['type'][0],
        'tmp_name' => $_FILES['files']['tmp_name'][0],
        'error' => $_FILES['files']['error'][0],
        'size' => $_FILES['files']['size'][0]
    ];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive in HTML form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        
        $message = isset($errorMessages[$file['error']]) ? $errorMessages[$file['error']] : 'Unknown upload error';
        sendResponse(false, $message);
    }
    
    // Validate file size
    $sizeValidation = validateFileSize($file['size']);
    if (!$sizeValidation['valid']) {
        sendResponse(false, $sizeValidation['message']);
    }
    
    // Validate file extension
    $extValidation = validateExtension($file['name']);
    if (!$extValidation['valid']) {
        sendResponse(false, $extValidation['message']);
    }
    
    // Validate MIME type
    $mimeValidation = validateMimeType($file['tmp_name']);
    if (!$mimeValidation['valid']) {
        sendResponse(false, $mimeValidation['message']);
    }
    
    // Scan file content for malicious patterns
    $contentScan = scanFileContent($file['tmp_name'], pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!$contentScan['safe']) {
        sendResponse(false, $contentScan['message']);
    }
    
    // Generate unique filename
    $uniqueFilename = generateUniqueFilename($file['name']);
    $uploadPath = UPLOAD_DIR . $uniqueFilename;
    
    // Move uploaded file to destination
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        sendResponse(false, 'Failed to move uploaded file');
    }
    
    // Set file permissions (read-only for security)
    chmod($uploadPath, 0644);
    
    // Generate file URL (adjust based on your domain)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    $fileUrl = $protocol . '://' . $host . $scriptPath . '/' . UPLOAD_DIR . $uniqueFilename;
    
    // Success response
    sendResponse(true, 'File uploaded successfully', $fileUrl);
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method');
}

// Handle file upload
handleFileUpload();

?>