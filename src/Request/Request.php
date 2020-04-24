<?php
namespace Piaic\Request;

/**
 * Class Request
 * @package Piaic\Request
 */
class Request
{
    public $matches;

    private $appDirPath;
    private $isHttps;
    private $domain;
    private $url;
    private $queryString;
    private $fullUrl;

    private $clientIp;
    private $serverIp;
    private $userAgent;

    private $method;
    private $defaultLanguage;
    private $language;
    private $package;
    private $route;
    private $handler;

    /**
     * Request constructor.
     * @param string $appDirPath
     * @param array $languages
     * @param array $packages
     */
    public function __construct(string $appDirPath, array $languages, array $packages)
    {
        $this->matches = [];

        $this->appDirPath = $appDirPath;
        $this->isHttps = 0 < strlen(filter_input(INPUT_SERVER, 'HTTPS'));
        $this->domain = filter_input(INPUT_SERVER, 'HTTP_HOST');
        $this->url = filter_input(INPUT_SERVER, 'REQUEST_URI');
        $this->queryString = filter_input(INPUT_SERVER, 'QUERY_STRING');
        $this->fullUrl = ($this->isHttps ? 'https://' : 'http://') . $this->domain . $this->url . ('' == $this->queryString ? '' : '?' . $this->queryString);

        $this->clientIp = filter_input_array(INPUT_SERVER)['HTTP_CLIENT_IP'] ?? filter_input_array(INPUT_SERVER)['HTTP_X_FORWARDED_FOR'] ?? filter_input_array(INPUT_SERVER)['REMOTE_ADDR'] ?? '';
        $this->serverIp = filter_input(INPUT_SERVER, 'SERVER_ADDR');
        $this->userAgent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');

        $this->method = 'GET';
        $this->defaultLanguage = $languages[0] ?? '';
        $this->language = '';
        $this->package = 'Index';
        $this->route = 'index';
        $this->handler = filter_input_array(INPUT_POST)['handler'] ?? filter_input_array(INPUT_GET)['handler'] ?? 'index';

        $url = trim($this->url, '/');
        if ('' != $url) {
            $urlParts = explode('/', $url);
            $index = 0;

            /**
             * Check language.
             */
            if (false !== ($key = array_search($urlParts[$index], $languages))) {
                $index++;
                $this->language = $languages[$key];
            }

            /**
             * Check package.
             */
            if (isset($urlParts[$index]) && false !== ($key = array_search(ucfirst($urlParts[$index]), $packages))) {
                $index++;
                $this->package = $packages[$key];
            }

            /**
             * Get route.
             */
            if (isset($urlParts[$index])) {
                for ($i = 0; $i < $index; $i++) {
                    unset($urlParts[$i]);
                }
                $this->route = implode('/', $urlParts);
                $this->route = trim(str_replace('.', '_', $this->route), '/');
            }
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->method = 'AJAX';
        } else {
            if (isset($_SERVER['REQUEST_METHOD']) && in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST', 'PUT', 'DELETE'])) {
                $this->method = $_SERVER['REQUEST_METHOD'];
            }
        }
    }

    public function appDirPath(): string
    {
        return $this->appDirPath;
    }

    public function isHttps(): bool
    {
        return $this->isHttps;
    }

    public function domain(): string
    {
        return $this->domain;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function queryString(): string
    {
        return $this->queryString;
    }

    public function fullUrl(): string
    {
        return $this->fullUrl;
    }

    public function clientIp(): string
    {
        return $this->clientIp;
    }

    public function serverIp(): string
    {
        return $this->serverIp;
    }

    public function userAgent(): string
    {
        return $this->userAgent;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function language(): string
    {
        return $this->language;
    }

    public function defaultLanguage(): string
    {
        return $this->defaultLanguage;
    }

    public function package(): string
    {
        return $this->package;
    }

    public function route(): string
    {
        return $this->route;
    }

    public function handler(): string
    {
        return $this->handler;
    }

    public function setLanguageToDefault()
    {
        $this->language = $this->defaultLanguage;
    }

    /**
     * @return string
     */
    public function packageDirPath(): string
    {
        return $this->appDirPath . 'package/' . $this->package;
    }

    public function makeUrl($route = '', $setDomain = false): string
    {
        $url = '/';
        if ($setDomain) {
            $url = ($this->isHttps ? 'https://' : 'http://') . $this->domain . '/';
        }
        if ('' != $this->language) {
            $url .= $this->language . '/';
        }
        if ('Index' != $this->package) {
            $url .= lcfirst($this->package) . '/';
        }
        if ('' != $route) {
            $url .= $route;
        } else {
            if ('index' != $this->route) {
                $url .= $this->route;
            }
            $url .= ('' == $this->queryString ? '' : '?' . $this->queryString);
        }

        return $url;
    }
}
