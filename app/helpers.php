<?php

use Illuminate\Support\Facades\Storage;

if (!function_exists('image_url')) {
    /**
     * Get image URL (handles both Cloudinary URLs and local storage paths)
     */
    function image_url($imagePath): string
    {
        if (!$imagePath) {
            return '';
        }

        // Cloudinary URLの場合はそのまま返す
        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
            return $imagePath;
        }
        
        // パスの場合はStorage::url()を使用
        return Storage::url($imagePath);
    }
}

