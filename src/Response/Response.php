<?php
namespace Piaic\Response;

use Piaic\Exception\PiaicException;

/**
 * Class Response
 * @package Piaic\Response
 */
class Response
{
    /**
     * Response constructor.
     */
    public function __construct()
    {
        $this->content = '';
        $this->notFound = true;
    }

    /**
     * @param string $content
     * @param bool $error
     */
    public function setContent(string $content, bool $error)
    {
        $this->notFound = false;
        $this->content = $content;
        if ($error) {
            http_response_code(500);
        }
    }

    /**
     * @param string $content
     * @param bool $error
     */
    public function setJson(string $content, bool $error)
    {
        $this->setContent($content, $error);
        header('Content-Type: application/json; charset=utf-8');
    }

    /**
     * @param string $packageDirPath
     * @param string $language
     * @throws PiaicException
     */
    public function set404(string $packageDirPath, string $language)
    {
        http_response_code(404);
        $this->content = '404 Not Found';
        if (file_exists($packageDirPath . '/view/404/view.html.php')) {
            $view = new View($packageDirPath, $language, '__404__', true);
            $this->content = $view->getView('404', [], false);
        }
    }

    public function isNotFound(): bool
    {
        return $this->notFound;
    }

    /**
     * Send response.
     */
    public function echo()
    {
        echo $this->content;

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif ('cli' !== php_sapi_name()) {
            $level = ob_get_level();
            if (0 < $level) {
                $status = ob_get_status(true);
                $flags = defined('PHP_OUTPUT_HANDLER_REMOVABLE') ? PHP_OUTPUT_HANDLER_REMOVABLE | PHP_OUTPUT_HANDLER_FLUSHABLE : -1;
                while ($level-- > 0 && ($s = $status[$level]) && (!isset($s['del']) ? !isset($s['flags']) || $flags === ($s['flags'] & $flags) : $s['del'])) {
                    ob_end_flush();
                }
            }
        }
    }

    /**
     * @param string $url
     * @param int $statusCode
     */
    public function redirect(string $url, int $statusCode = 303)
    {
        $this->notFound = false;

        if (!headers_sent()) {
            header('Location: ' . $url, true, $statusCode);
        } else {
            echo sprintf(
                '<!DOCTYPE html><html><head><meta charset="UTF-8" /><meta http-equiv="refresh" content="0;url=%1$s" /><title>Redirecting to %1$s</title></head><body><script type="text/javascript"> window.location.href = "%1$s"; </script>Redirecting to <a href="%1$s">%1$s</a>.</body></html>',
                htmlspecialchars($url, ENT_QUOTES, 'UTF-8')
            );
        }
    }

    private $content;
    private $notFound;
}