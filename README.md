
# Secure File Uploader

A modern, secure, and user-friendly file uploading web application with shareable links.

![Secure File Uploader](https://raw.githubusercontent.com/iamshakhawat/Ajax-File-Uploader-with-PHP/refs/heads/main/uplaod.png)

## Features

- 🔒 **Secure Uploads** - Multiple security checks including MIME type validation and malicious content scanning
- 📤 **Drag & Drop** - Intuitive drag-and-drop interface for file uploads
- 🔗 **Shareable Links** - Generate unique, secure download links for uploaded files
- 📊 **File Management** - View, organize, and delete uploaded files
- 🎨 **Modern UI** - Beautiful, responsive design with TailwindCSS
- ⚡ **Fast Performance** - Optimized for speed with AJAX uploads
- 🛡️ **Type Safety** - Comprehensive file type validation and blocking of dangerous extensions

## Supported File Types

Images, Documents, Videos, Audio, Archives, Code Files, and more:
- Images: JPG, JPEG, PNG, GIF, SVG, WebP
- Documents: PDF, DOC, DOCX, TXT, XLS, XLSX, PPT, PPTX
- Media: MP4, AVI, MOV, MP3, WAV
- Archives: ZIP, RAR
- Code: JSON, XML, HTML, CSS, JS

## Installation

### Requirements

- PHP 7.4 or higher
- Web server (Apache, Nginx, etc.)
- 100MB+ free disk space

### Steps

1. **Clone the repository**
    ```bash
    git clone https://github.com/yourusername/secure-file-uploader.git
    cd secure-file-uploader
    ```

2. **Create upload directory**
    ```bash
    mkdir u
    chmod 755 u
    ```

3. **Configure web server** - Point document root to project directory

4. **Access the application**
    ```
    http://localhost/share/
    ```

## Usage

### Upload Files

1. Navigate to the upload page
2. Drag files into the drop zone or click "Browse Files"
3. Review selected files
4. Click "Upload Files"
5. Copy shareable links from success modal

### View Files

Click "View All Files" to see uploaded files, their sizes, and upload dates.

### Delete Files

- Double-click the file icon to enable delete mode
- Select files and delete individually or all at once

## Configuration

Edit upload settings in PHP files:

```php
// upload.php
define('MAX_FILE_SIZE', 104857600);  // 100MB
define('UPLOAD_DIR', './u/');        // Upload directory
```

## Security Features

✅ File extension validation  
✅ MIME type checking  
✅ Malicious content scanning  
✅ File size limits  
✅ Directory traversal prevention  
✅ Unique filename generation  

## Project Structure

```
secure-file-uploader/
├── index.html          # Upload interface
├── download.html       # File management
├── upload.php          # Upload handler
├── get_files.php       # Retrieve files list
├── delete.php          # Delete handler
├── u/                  # Upload directory
└── readme.md           # This file
```

## API Endpoints

### POST /upload.php
Upload a file. Returns success status and shareable link.

### GET /get_files.php
Retrieve all uploaded files with metadata.

### POST /delete.php
Delete file(s). Accepts `filename` or `delete_all` parameter.

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## Development

Feel free to submit issues and enhancement requests!

## License

MIT License - see LICENSE file for details

## Support

For issues or questions, please open a GitHub issue.

---

Made with ❤️ by the community
