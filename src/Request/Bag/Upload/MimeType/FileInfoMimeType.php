<?php
namespace Piaic\Request\Bag\Upload\MimeType;

/**
 * Class FileInfoMimeType
 * @package Piaic\Request\Bag\Upload\MimeType
 */
class FileInfoMimeType
{
    /**
     * @return bool
     */
    public static function isSupported(): bool
    {
        return function_exists('finfo_open');
    }

    /**
     * @param string $path
     * @return string
     */
    public function guess(string $path): string
    {
        if (!self::isSupported()) {
            return '';
        }

        if (!$fileInfo = new \finfo(FILEINFO_MIME_TYPE)) {
            return '';
        }

        return $fileInfo->file($path);
    }
}