<?php
namespace Piaic\Request\Bag\Upload\MimeType;

/**
 * Class BinaryMimeType
 * @package Piaic\Request\Bag\Upload\MimeType
 */
class BinaryMimeType
{
    /**
     * @return bool
     */
    public static function isSupported(): bool
    {
        return '\\' !== DIRECTORY_SEPARATOR && function_exists('passthru') && function_exists('escapeshellarg');
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

        $return = 1;
        ob_start();
        passthru(sprintf('file -b --mime %s 2>/dev/null', escapeshellarg($path)), $return);
        if ($return > 0) {
            ob_end_clean();
            return '';
        }

        $type = trim(ob_get_clean());
        if (!preg_match('#^([a-z0-9\-]+/[a-z0-9\-\.]+)#i', $type, $match)) {
            return '';
        }

        return $match[1];
    }
}