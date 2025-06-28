<?php

namespace App\Handlers;

use Exception;
use finfo;

class FileUploadHandler
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'application/pdf' => 'pdf',
        'image/heic' => 'heic'
    ];

    private const MAX_FILE_SIZE = 5242880; // 5MB
    private const UPLOAD_PATH = 'public/uploads/';
    private const ALLOWED_DIMENSIONS = [
        'max_width' => 4096,
        'max_height' => 4096
    ];

    /**
     * Handle file upload with security checks
     * @param array $file The uploaded file array from $_FILES
     * @param string $subDirectory Optional subdirectory within uploads
     * @return array Upload result with file info
     * @throws Exception If file is invalid or upload fails
     */
    public function handleUpload(array $file, string $subDirectory = ''): array
    {
        try {
            // Validate file presence
            if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                throw new Exception('Invalid file upload');
            }

            // Validate file size
            if ($file['size'] > self::MAX_FILE_SIZE) {
                throw new Exception('File size exceeds limit of 5MB');
            }

            // Validate MIME type
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            
            if (!array_key_exists($mimeType, self::ALLOWED_MIME_TYPES)) {
                throw new Exception('Invalid file type');
            }

            // Generate secure filename
            $extension = self::ALLOWED_MIME_TYPES[$mimeType];
            $filename = $this->generateSecureFilename($extension);

            // Create upload directory if it doesn't exist
            $uploadDir = self::UPLOAD_PATH . trim($subDirectory, '/');
            if (!empty($subDirectory) && !is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $uploadPath = $uploadDir . '/' . $filename;

            // Additional security checks for images
            if (strpos($mimeType, 'image/') === 0) {
                $this->validateImage($file['tmp_name']);
            }

            // Scan for malware (implement with proper antivirus integration)
            $this->scanForMalware($file['tmp_name']);

            // Move file to final location
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new Exception('Failed to move uploaded file');
            }

            // Set proper permissions
            chmod($uploadPath, 0644);

            return [
                'filename' => $filename,
                'path' => $uploadPath,
                'mime_type' => $mimeType,
                'size' => $file['size']
            ];
        } catch (Exception $e) {
            // Clean up temporary file if it exists
            if (isset($file['tmp_name']) && file_exists($file['tmp_name'])) {
                @unlink($file['tmp_name']);
            }
            throw $e;
        }
    }

    /**
     * Generate a secure, unique filename
     */
    private function generateSecureFilename(string $extension): string
    {
        return sprintf(
            '%s_%s.%s',
            bin2hex(random_bytes(16)),
            time(),
            $extension
        );
    }

    /**
     * Validate image dimensions and content
     */
    private function validateImage(string $filepath): void
    {
        $imageInfo = @getimagesize($filepath);
        if ($imageInfo === false) {
            throw new Exception('Invalid image file');
        }

        // Check dimensions
        if ($imageInfo[0] > self::ALLOWED_DIMENSIONS['max_width'] ||
            $imageInfo[1] > self::ALLOWED_DIMENSIONS['max_height']) {
            throw new Exception('Image dimensions exceed maximum allowed');
        }

        // Validate image content
        if (!$this->isValidImage($filepath, $imageInfo[2])) {
            throw new Exception('Invalid image content');
        }
    }

    /**
     * Deep validation of image content
     */
    private function isValidImage(string $filepath, int $type): bool
    {
        switch ($type) {
            case IMAGETYPE_JPEG:
                $image = @imagecreatefromjpeg($filepath);
                break;
            case IMAGETYPE_PNG:
                $image = @imagecreatefrompng($filepath);
                break;
            default:
                return false;
        }

        if (!$image) {
            return false;
        }

        imagedestroy($image);
        return true;
    }

    /**
     * Scan file for malware
     * This is a placeholder - implement with proper antivirus integration
     */
    private function scanForMalware(string $filepath): void
    {
        // Implement virus scanning here
        // Example integration with ClamAV:
        /*
        $scanner = new ClamAV('localhost', 3310);
        $result = $scanner->scanFile($filepath);
        if ($result !== true) {
            throw new Exception('Malware detected in file');
        }
        */
    }

    /**
     * Clean up old temporary files
     */
    public function cleanupTempFiles(): void
    {
        $tempPath = sys_get_temp_dir();
        if ($handle = opendir($tempPath)) {
            while (false !== ($file = readdir($handle))) {
                $filepath = $tempPath . '/' . $file;
                if (is_file($filepath)) {
                    $mtime = filemtime($filepath);
                    if ($mtime && (time() - $mtime) > 86400) { // 24 hours
                        @unlink($filepath);
                    }
                }
            }
            closedir($handle);
        }
    }
}