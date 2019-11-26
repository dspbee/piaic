<?php
namespace Piaic\Application;

/**
 * Class View
 * @package Piaic\Application
 */
class View
{
    /**
     * View constructor.
     * @param string $packageDirPath
     * @param string $language
     * @param string $route
     * @param bool $cache
     */
    public function __construct(string $packageDirPath, string $language, string $route, bool $cache) {
        $this->packageDirPath = rtrim($packageDirPath, '/');
        $this->language = $language;
        $this->route = $route;
        $this->cache = $cache;
    }

    /**
     * @param string $name
     * @param array $data
     * @param bool $processLang
     * @return string
     * @throws PiaicException
     */
    public function getView(string $name, array $data = [], $processLang = true): string
    {
        $this->storagePathList = [];

        if (!in_array($name, ['view', 'error', 'success', 'info'])) {
            $name = 'view';
            $viewPath = $this->packageDirPath . '/Route/' . $this->route . '/' . $name . '.html.php';
        } else {
            $viewPath = $this->packageDirPath . '/view/' . $name . '/view.html.php';
        }

        if ($this->cache) {
            $pathToCachedView = $this->packageDirPath . '/view/_cache/' . str_replace(['/', '\\'], '_', $this->route) . '_' . $this->language . '.html.php';
            if (!file_exists($pathToCachedView)) {
                $content = $this->combine($viewPath, true, true);
                if ($processLang) {
                    $content = $this->replaceLangFromStorage($content);
                }
                $content = preg_replace(['/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s'], ['>', '<', '\\1'], $content);

                $fh = fopen($pathToCachedView, 'wb');
                if (flock($fh, LOCK_EX)) {
                    ftruncate($fh, 0);
                    fwrite($fh, $content);
                    fflush($fh);
                    flock($fh, LOCK_UN);
                } else {
                    throw new PiaicException('Can\'t write view cache');
                }
                fclose($fh);
            }

            $fh = fopen($pathToCachedView, 'rb');
            if (flock($fh, LOCK_SH)) {
                if (count($data)) {
                    $content = self::renderTemplate($pathToCachedView, $data);
                } else {
                    ob_start();
                    include $pathToCachedView;
                    $content = ob_get_clean();
                }
                flock($fh, LOCK_UN);
                fclose($fh);
                return $content;
            }
        } else {
            $content = $this->combine($viewPath, true, true);
            if ($processLang) {
                $content = $this->replaceLangFromStorage($content);
            }
            $content = preg_replace(['/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s'], ['>', '<', '\\1'], $content);

            if (count($data)) {
                $fh = tmpfile();
                fwrite($fh, $content);
                $content = self::renderTemplate(stream_get_meta_data($fh)['uri'], $data);
                fclose($fh);
                return $content;
            } else {
                return $content;
            }
        }

        throw new PiaicException('Can\'t render template');
    }

    /**
     * @param string $path
     * @param bool $processInclude
     * @param bool $processExtends
     * @return mixed|string
     * @throws PiaicException
     */
    private function combine(string $path, bool $processInclude, bool $processExtends)
    {
        if (file_exists($path)) {
            ob_start();
            readfile($path);
            $content = ob_get_clean();
        } else {
            throw new PiaicException('Path not found: ' . $path);
        }

        $storagePath = explode('/', $path);
        array_pop($storagePath);
        $storagePath = implode('/', $storagePath) . '/' . $this->language . '.php';

        if (file_exists($storagePath)) {
            $this->storagePathList[] = $storagePath;
        }

        if ($processInclude) {
            preg_match_all('/<!-- include (.*) -->/', $content, $matchList);
            if (isset($matchList[1])) {
                foreach ($matchList[1] as $key => $view) {
                    if (!empty($matchList[0][$key]) && false !== strpos($content, $matchList[0][$key])) {
                        $view = trim($view);
                        $content = str_replace($matchList[0][$key], $this->combine($this->packageDirPath . '/view/' . $view . '/view.html.php', true, false), $content);
                    }
                }
            }
        }

        if ($processExtends) {
            preg_match_all('/<!-- extends (.*) -->/', $content, $matchList);
            if (isset($matchList[1][0])) {
                $view = trim($matchList[1][0]);
                $parentHtml = $this->combine($this->packageDirPath . '/view/' . $view . '/view.html.php', true, false);

                $content = str_replace($matchList[0][0], '', $content);
                $parentHtml = str_replace('<!-- section -->', $content, $parentHtml);
                $content = $parentHtml;
            }
        }

        return $content;
    }

    /**
     * @param string $content
     * @return string
     */
    private function replaceLangFromStorage(string $content): string
    {
        $storage = [];
        foreach ($this->storagePathList as $path) {
            $temp = include $path;
            foreach ($temp as $k => $v) {
                if (!isset($storage[$k])) {
                    $storage[$k] = $v;
                }
            }
        }

        preg_match_all('/<!-- lang (.*) -->/', $content, $matchList);
        if (isset($matchList[1])) {
            foreach ($matchList[1] as $key => $index) {
                $name = explode('>', $index);
                $default = trim($name[1] ?? '');
                $name = trim($name[0]);
                if (!empty($matchList[0][$key]) && false !== strpos($content, $matchList[0][$key])) {
                    $content = str_replace($matchList[0][$key], $storage[$name] ?? $default, $content);
                }
            }
        }

        return $content;
    }

    /**
     * Safe include. Used for scope isolation.
     *
     * @param string $__file__  File to include
     * @param array  $data      Data passed to template
     * @return string
     */
    private static function renderTemplate(string $__file__, array $data): string
    {
        ob_start();
        extract($data);
        include $__file__;
        return ob_get_clean();
    }

    private $packageDirPath;
    private $language;
    private $route;
    private $cache;
    private $storagePathList;
}