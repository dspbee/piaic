<?php
namespace Piaic\Application;

/**
 * Class Debug
 * @package Piaic\Application
 */
class Debug
{
    /**
     * @return bool
     */
    public static function isEnabled()
    {
        return self::$debugEnabled;
    }

    /**
     * @param string $appDirPath
     */
    public static function setPackageRoot(string $appDirPath)
    {
        self::$packageDirPath = $appDirPath . 'package/Piaic';
    }

    /**
     * Register error handle.
     */
    public static function register()
    {
        self::$debugEnabled = true;
        set_error_handler('Piaic\Application\Debug::render');
        register_shutdown_function(['Piaic\Application\Debug', 'handleFatal']);
    }

    /**
     * @throws PiaicException
     */
    public static function handleFatal()
    {
        $error = error_get_last();
        if(null !== $error) {
            self::render($error["type"], $error["message"], $error["file"], $error["line"]);
        }
    }

    /**
     * @param \Throwable $e
     * @throws PiaicException
     */
    public static function handleException(\Throwable $e)
    {
        self::render($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine(), null, $e->getTrace(), get_class($e));
    }

    /**
     * @param string $code
     * @param string $message
     * @param string $file
     * @param string $line
     * @param null $context
     * @param array $backtrace
     * @param string $className
     * @throws PiaicException
     */
    public static function render(string $code, string $message, string $file, string $line, $context = null, array $backtrace = [], string $className = '')
    {
        if (ob_get_length()) {
            ob_clean();
        }
        $data = [
            'message' => 'From ' . $className . ' ' . $message,
            'code' => $code,
            'file' => $file,
            'line' => $line,
            'trace' => $backtrace,
            'context' => $context
        ];

        if (!empty($data['trace'])) {
            foreach ($data['trace'] as $k => $item) {
                if (isset($item['type'])) {
                    switch ($item['type']) {
                        case '->':
                            $data['trace'][$k]['type'] = 'method';
                            break;
                        case '::':
                            $data['trace'][$k]['type'] = 'static method';
                            break;
                        default:
                            $data['trace'][$k]['type'] = 'function';
                    }
                }
            }
        }

        $view = new View(self::$packageDirPath, '', '', false);
        $content = $view->getView('debug', $data, false);

        $response = new Response();
        $response->setTextHtml($content, true);
        $response->echo();
        exit;
    }

    private static $debugEnabled = false;
    private static $packageDirPath = '';
}