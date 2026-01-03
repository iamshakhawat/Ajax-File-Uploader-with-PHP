<?php
/**
 * File Deletion Handler
 * This script handles deletion of single files or all files from the upload directory
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
function sendResponse($success, $message) {
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit;
}

/**
 * Validate filename to prevent directory traversal attacks
 */
function isValidFilename($filename) {
    // Check for directory traversal attempts
    if (strpos($filename, '..') !== false || 
        strpos($filename, '/') !== false || 
        strpos($filename, '\\') !== false) {
        return false;
    }
    
    // Check if filename is not empty
    if (empty($filename)) {
        return false;
    }
    
    return true;
}

/**
 * Delete a single file
 */
function deleteSingleFile($filename) {
    // Validate filename
    if (!isValidFilename($filename)) {
        sendResponse(false, 'Invalid filename');
    }
    
    // Construct full file path
    $filePath = UPLOAD_DIR . $filename;
    
    // Check if file exists
    if (!file_exists($filePath)) {
        sendResponse(false, 'File does not exist');
    }
    
    // Check if it's actually a file (not a directory)
    if (!is_file($filePath)) {
        sendResponse(false, 'Invalid file');
    }
    
    // Attempt to delete the file
    if (unlink($filePath)) {
        sendResponse(true, 'File deleted successfully');
    } else {
        sendResponse(false, 'Failed to delete file. Check permissions.');
    }
}

/**
 * Delete all files in upload directory
 */
function deleteAllFiles() {
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
    
    // Counter for deleted files
    $deletedCount = 0;
    $failedCount = 0;
    
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
            // Attempt to delete the file
            if (unlink($filePath)) {
                $deletedCount++;
            } else {
                $failedCount++;
            }
        }
    }
    
    // Send response based on results
    if ($deletedCount > 0 && $failedCount === 0) {
        sendResponse(true, "Successfully deleted all {$deletedCount} file(s)");
    } elseif ($deletedCount > 0 && $failedCount > 0) {
        sendResponse(true, "Deleted {$deletedCount} file(s), but {$failedCount} file(s) could not be deleted");
    } elseif ($deletedCount === 0 && $failedCount > 0) {
        sendResponse(false, "Failed to delete {$failedCount} file(s)");
    } else {
        sendResponse(true, 'No files to delete');
    }
}

/**
 * Main execution
 */

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method. Use POST.');
}

// Check if delete_all parameter is set
if (isset($_POST['delete_all']) && $_POST['delete_all'] === 'true') {
    // Delete all files
    deleteAllFiles();
}

// Check if filename parameter is set
if (isset($_POST['filename']) && !empty($_POST['filename'])) {
    // Delete single file
    $filename = $_POST['filename'];
    deleteSingleFile($filename);
}

// If we reach here, no valid action was specified
sendResponse(false, 'No valid action specified');

?>