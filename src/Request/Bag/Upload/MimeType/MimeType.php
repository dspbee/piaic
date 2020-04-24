<?php
namespace Piaic\Request\Bag\Upload\MimeType;

use Piaic\Exception\PiaicException;

/**
 * Class MimeType
 * @package Piaic\Request\Bag\Upload\MimeType
 */
class MimeType
{
    private static $instance = null;
    private $guesserList;

    /**
     * @return MimeType
     */
    public static function getInstance(): MimeType
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * @param string $path Path to the file
     * @return string
     * @throws PiaicException
     */
    public function guess(string $path): string
    {
        if (!is_file($path)) {
            throw new PiaicException('File not found ' . $path);
        }

        if (!is_readable($path)) {
            throw new PiaicException('File not readable ' . $path);
        }

        if (0 == count($this->guesserList)) {
            $msg = 'Unable to guess the mime type as no guessers are available';
            if (!FileInfoMimeType::isSupported()) {
                $msg .= ' (Did you enable the php_fileinfo extension?)';
            }
            throw new PiaicException($msg);
        }

        /** @var FileInfoMimeType|BinaryMimeType $guesser */
        foreach ($this->guesserList as $guesser) {
            if ('' != $mimeType = $guesser->guess($path)) {
                return $mimeType;
            }
        }

        return '';
    }


    /**
     * Registers all natively provided mime type guessers.
     */
    private function __construct()
    {
        $this->guesserList = [];

        if (FileInfoMimeType::isSupported()) {
            $this->guesserList[] = new FileInfoMimeType();
        }
        if (BinaryMimeType::isSupported()) {
            $this->guesserList[] = new BinaryMimeType();
        }
    }
}