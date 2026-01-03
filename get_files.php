<?php
/**
 * Get Files Handler
 * This script retrieves all files from the upload directory and returns them as JSON
 */

// Set response header to JSON
header('Content-Type: application/json');

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Configuration
define('UPLOAD_DIR', './u/');  // Upload directory

/**
 * Send JSON response
 */
function sendResponse($success, $message, $files = []) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'files' => $files
    ]);
    exit;
}

/**
 * Get all files from upload directory
 */
function getFiles() {
    // Check if upload directory exists
    if (!file_exists(UPLOAD_DIR)) {
        sendResponse(false, 'Upload directory does not exist');
    }
    
    // Check if directory is readable
    if (!is_readable(UPLOAD_DIR)) {
        sendResponse(false, 'Upload directory is not readable');
    }
    
    // Scan directory for files
    $allItems = scandir(UPLOAD_DIR);
    
    if ($allItems === false) {
        sendResponse(false, 'Failed to read upload directory');
    }
    
    // Array to store file information
    $files = [];
    
    // Loop through all items in directory
    foreach ($allItems as $item) {
        // Skip current and parent directory references
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        // Get full path
        $filePath = UPLOAD_DIR . $item;
        
        // Check if it's a file (not a directory)
        if (is_file($filePath)) {
            // Get file information
            $fileInfo = getFileInfo($item, $filePath);
            
            if ($fileInfo) {
                $files[] = $fileInfo;
            }
        }
    }
    
    // Sort files by modified time (newest first - DESC)
    usort($files, function($a, $b) {
        return $b['modified'] - $a['modified'];
    });
    
    // Return files
    sendResponse(true, 'Files retrieved successfully', $files);
}

/**
 * Get file information
 */
function getFileInfo($fileName, $filePath) {
    // Check if file exists
    if (!file_exists($filePath)) {
        return false;
    }
    
    // Get file stats
    $fileStats = stat($filePath);
    
    if ($fileStats === false) {
        return false;
    }
    
    // Get file size
    $fileSize = filesize($filePath);
    
    // Get file modification time
    $modifiedTime = filemtime($filePath);
    
    // Generate file URL
    $fileUrl = generateFileUrl($fileName);
    
    // Return file information array
    return [
        'name' => $fileName,
        'size' => $fileSize,
        'modified' => $modifiedTime,
        'url' => $fileUrl
    ];
}

/**
 * Generate file URL
 */
function generateFileUrl($fileName) {
    // Get protocol (http or https)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    
    // Get host
    $host = $_SERVER['HTTP_HOST'];
    
    // Get script directory path
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    
    // Remove trailing slash from script path if exists
    $scriptPath = rtrim($scriptPath, '/');
    
    // Construct full URL
    $fileUrl = $protocol . '://' . $host . $scriptPath . '/' . UPLOAD_DIR . $fileName;
    
    return $fileUrl;
}

/**
 * Main execution
 */

// Check if request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(false, 'Invalid request method');
}

// Get and return files
getFiles();

?>