<?php
namespace Piaic\Request\Bag;

use Piaic\Exception\PiaicException;
use Piaic\Request\Bag\Upload\FileUpload;

/**
 * Class FileBag
 * @package Piaic\Request\Bag
 */
class FileBag
{
    private $bag;

    /**
     * FileBag constructor.
     * @throws PiaicException
     */
    public function __construct()
    {
        $this->bag = [];
        foreach ($_FILES as $key => $file) {
            if (!is_array($file) && !$file instanceof FileUpload) {
                throw new PiaicException('An uploaded file must be an array or an instance of FileUpload.');
            }
            $this->bag[$key] = $this->convertFileInformation($file);
        }
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->bag);
    }

    /**
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->bag);
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public function fetch(string $key, $default = null)
    {
        return $this->bag[$key] ?? $default;
    }

    /**
     * @param $file
     * @return array|FileUpload|null
     * @throws PiaicException
     */
    private function convertFileInformation($file)
    {
        if ($file instanceof FileUpload) {
            return $file;
        }

        $file = $this->fixPhpFilesArray($file);
        if (is_array($file)) {
            $keys = array_keys($file);
            sort($keys);

            if ($keys == ['error', 'name', 'size', 'tmp_name', 'type']) {
                if (UPLOAD_ERR_NO_FILE == $file['error']) {
                    $file = null;
                } else {
                    $file = new FileUpload($file['tmp_name'], $file['error']);
                }
            } else {
                $file = array_map([$this, 'convertFileInformation'], $file);
            }
        }

        return $file;
    }

    /**
     * @param array $data
     * @return array
     */
    private function fixPhpFilesArray(array $data): array
    {
        if (!is_array($data)) {
            return $data;
        }

        $keys = array_keys($data);
        sort($keys);

        if (['error', 'name', 'size', 'tmp_name', 'type'] != $keys || !isset($data['name']) || !is_array($data['name'])) {
            return $data;
        }

        $files = $data;
        foreach (['error', 'name', 'size', 'tmp_name', 'type'] as $k) {
            unset($files[$k]);
        }

        foreach ($data['name'] as $key => $name) {
            $files[$key] = $this->fixPhpFilesArray(array(
                'error' => $data['error'][$key],
                'name' => $name,
                'type' => $data['type'][$key],
                'tmp_name' => $data['tmp_name'][$key],
                'size' => $data['size'][$key],
            ));
        }

        return $files;
    }
}