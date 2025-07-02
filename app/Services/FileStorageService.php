<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class FileStorageService
{
    /**
     * Allowed file types dengan MIME types
     */
    private const ALLOWED_TYPES = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
        'video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv', 'video/webm',
        'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/zip', 'application/x-rar-compressed',
    ];

    /**
     * Maximum file size (dalam bytes)
     */
    private const MAX_FILE_SIZE = [
        'image' => 5 * 1024 * 1024,    // 5MB untuk image
        'video' => 100 * 1024 * 1024,  // 100MB untuk video
        'document' => 10 * 1024 * 1024, // 10MB untuk document
        'default' => 20 * 1024 * 1024   // 20MB default
    ];

    /**
     * Storage disk yang digunakan
     */
    private string $disk;

    public function __construct(string $disk = 'public')
    {
        $this->disk = $disk;
    }

    /**
     * Upload single file
     */
    public function uploadFile(UploadedFile $file, string $directory = 'uploads'): array
    {
        try {
            // Validasi file
            $this->validateFile($file);

            // Generate unique filename
            $filename = $this->generateUniqueFilename($file);

            // Tentukan path lengkap
            $path = $directory . '/' . date('Y/m/d');
            $fullPath = $path . '/' . $filename;

            // Upload file ke storage
            $storedPath = Storage::disk($this->disk)->putFileAs($path, $file, $filename);

            if (!$storedPath) {
                throw new Exception('Failed to upload file');
            }

            return [
                'success' => true,
                'data' => [
                    'filename' => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $storedPath,
                    'url' => Storage::disk($this->disk)->url($storedPath),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'extension' => $file->getClientOriginalExtension(),
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Upload multiple files
     */
    public function uploadMultipleFiles(array $files, string $directory = 'uploads'): array
    {
        $results = [];
        $successCount = 0;
        $failCount = 0;

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $result = $this->uploadFile($file, $directory);

                if ($result['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                }

                $results[] = $result;
            }
        }

        return [
            'success' => $failCount === 0,
            'summary' => [
                'total' => count($files),
                'success' => $successCount,
                'failed' => $failCount
            ],
            'results' => $results
        ];
    }

    /**
     * Delete file
     */
    public function deleteFile(string $path): bool
    {
        try {
            if (Storage::disk($this->disk)->exists($path)) {
                return Storage::disk($this->disk)->delete($path);
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get file URL
     */
    public function getFileUrl(string $path): ?string
    {
        try {
            if (Storage::disk($this->disk)->exists($path)) {
                return Storage::disk($this->disk)->url($path);
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Check if file exists
     */
    public function fileExists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    /**
     * Get file size
     */
    public function getFileSize(string $path): ?int
    {
        try {
            if (Storage::disk($this->disk)->exists($path)) {
                return Storage::disk($this->disk)->size($path);
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Validate uploaded file
     */
    private function validateFile(UploadedFile $file): void
    {
        // Check if file is valid
        if (!$file->isValid()) {
            throw new Exception('Invalid file upload');
        }

        // Check file type
        if (!in_array($file->getMimeType(), self::ALLOWED_TYPES)) {
            throw new Exception('File type not allowed: ' . $file->getMimeType());
        }

        // Check file size
        $fileType = $this->getFileType($file->getMimeType());
        $maxSize = self::MAX_FILE_SIZE[$fileType] ?? self::MAX_FILE_SIZE['default'];

        if ($file->getSize() > $maxSize) {
            $maxSizeMB = round($maxSize / 1024 / 1024, 2);
            throw new Exception("File size exceeds maximum allowed size of {$maxSizeMB}MB");
        }
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('YmdHis');
        $random = Str::random(8);

        return "{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Get file type category
     */
    private function getFileType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        } elseif (in_array($mimeType, ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
            return 'document';
        }

        return 'default';
    }
}
