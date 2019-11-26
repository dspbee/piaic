<?php
namespace Piaic\Application;

/**
 * Class Request
 * @package Piaic\Application
 */
class Request
{
    public $appDirPath;
    public $https;
    public $domain;
    public $url;
    public $queryString;
    public $fullUrl;

    public $defaultLanguage;
    public $clientIp;
    public $serverIp;

    public $method;
    public $language;
    public $package;
    public $route;
    public $indexRoute;
    public $handler;

    /**
     * Request constructor.
     * @param array $languages
     * @param array $packages
     */
    public function __construct(array $languages, array $packages)
    {
        $this->https = 0 < strlen(filter_input(INPUT_SERVER, 'HTTPS'));
        $this->domain = filter_input(INPUT_SERVER, 'HTTP_HOST');
        $this->url = filter_input(INPUT_SERVER, 'REQUEST_URI');
        $this->queryString = filter_input(INPUT_SERVER, 'QUERY_STRING');
        $this->fullUrl = ($this->https ? 'https://' : 'http://') . $this->domain . '/' . $this->url . ('' == $this->queryString ? '' : '?' . $this->queryString);

        $this->defaultLanguage = $languages[0] ?? '';
        $this->clientIp = filter_input_array(INPUT_SERVER)['HTTP_CLIENT_IP'] ?? filter_input_array(INPUT_SERVER)['HTTP_X_FORWARDED_FOR'] ?? filter_input_array(INPUT_SERVER)['REMOTE_ADDR'] ?? '';
        $this->serverIp = filter_input(INPUT_SERVER, 'SERVER_ADDR');

        $this->method = 'GET';
        $this->language = '';
        $this->package = 'Main';
        $this->route = 'index';
        $this->indexRoute = true;
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

        if ('index' != $this->route) {
            $this->indexRoute = false;
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $this->method = 'AJAX';
        } else {
            if (isset($_SERVER['REQUEST_METHOD']) && in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST', 'PUT', 'DELETE'])) {
                $this->method = $_SERVER['REQUEST_METHOD'];
            }
        }
    }

}