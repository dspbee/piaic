<?php
namespace Piaic\Request\Bag\Upload;

use Piaic\Exception\PiaicException;
use Piaic\Request\Bag\Upload\Extension\Extension;
use Piaic\Request\Bag\Upload\MimeType\MimeType;

/**
 * Class FileUpload
 * @package Piaic\Request\Bag\Upload
 */
class FileUpload extends \SplFileInfo
{
    const ERRORS = [
        UPLOAD_ERR_INI_SIZE => 'The file exceeds your upload_max_filesize ini directive.',
        UPLOAD_ERR_FORM_SIZE => 'The file exceeds the upload limit defined in your form.',
        UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_CANT_WRITE => 'The file could not be written on disk.',
        UPLOAD_ERR_NO_TMP_DIR => 'File could not be uploaded: missing temporary directory.',
        UPLOAD_ERR_EXTENSION => 'File upload was stopped by a PHP extension.',
    ];

    private $error;

    /**
     * FileUpload constructor.
     * @param string $path
     * @param $error
     * @throws PiaicException
     */
    public function __construct(string $path, $error)
    {
        $this->error = $error ?: UPLOAD_ERR_OK;
        if (UPLOAD_ERR_OK == $this->error && !is_file($path)) {
            throw new PiaicException('File not found ' . $path);
        }
        parent::__construct($path);
    }

    /**
     * @return string
     * @throws PiaicException
     */
    public function guessExtension(): string
    {
        return Extension::getInstance()->guess($this->guessMimeType());
    }

    /**
     * @return string
     * @throws PiaicException
     */
    public function guessMimeType(): string
    {
        return MimeType::getInstance()->guess($this->getPathname());
    }

    /**
     * @return string
     */
    public function read(): string
    {
        $data = '';
        $fileObj = $this->openFile();
        while (!$fileObj->eof()) {
            $data .= $fileObj->fread(4096);
        }
        $fileObj = null;
        return $data;
    }

    /**
     * @param string $directory
     * @param string $name
     * @return FileUpload
     * @throws PiaicException
     */
    public function move(string $directory, string $name = ''): FileUpload
    {
        if ($this->error === UPLOAD_ERR_OK && is_uploaded_file($this->getPathname())) {
            $target = $this->createTargetFile($directory, $name);
            if (!@move_uploaded_file($this->getPathname(), $target)) {
                $error = error_get_last();
                throw new PiaicException(sprintf('Could not move the file "%s" to "%s" (%s)', $this->getPathname(), $target, strip_tags($error['message'])));
            }
            $this->chmod($target);
            return $target;
        }
        throw new PiaicException($this->errorMessage());
    }

    /**
     * @return int
     */
    public function error(): int
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function errorMessage(): string
    {
        return self::ERRORS[$this->error] ?? 'The file was not uploaded due to an unknown error ' . $this->error;
    }

    /**
     * @param string $directory
     * @param string $name
     * @return FileUpload
     * @throws PiaicException
     */
    private function createTargetFile(string $directory, string $name = ''): FileUpload
    {
        if (!is_dir($directory)) {
            if (false === @mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new PiaicException(sprintf('Unable to create the "%s" directory', $directory));
            }
        } elseif (!is_writable($directory)) {
            throw new PiaicException(sprintf('Unable to write in the "%s" directory', $directory));
        }

        $target = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . ('' == $name ? $this->getBasename() : $this->clearName($name));

        return new self($target, 0);
    }

    /**
     * @param string $target
     * @param int $mode
     * @throws PiaicException
     */
    private function chmod(string $target, $mode = 0666)
    {
        if (false === @chmod($target, $mode & ~umask())) {
            throw new PiaicException(sprintf('Unable to change mode of the "%s"', $target));
        }
    }

    /**
     * @param string $name
     * @return string
     */
    private function clearName(string $name): string
    {
        $clearName = str_replace('\\', '/', $name);
        $pos = strrpos($clearName, '/');
        return false === $pos ? $clearName : substr($clearName, $pos + 1);
    }
}