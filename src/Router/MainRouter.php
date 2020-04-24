<?php
namespace Piaic\Router;

use Piaic\Controller\Controller;
use Piaic\Exception\PiaicException;
use Piaic\Request\Request;

/**
 * Class MainRouter
 * @package Piaic\Router
 */
class MainRouter implements Router
{
    protected $request;

    /**
     * MainRouter constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Controller
     * @throws PiaicException
     */
    public function findController(): Controller
    {
        $route = str_replace('/', '_', $this->request->route());
        $controllerClass = "{$this->request->package()}\\Route_{$route}\\{$this->request->method()}";
        $path = $this->request->appDirPath() . 'package/' . $this->request->package() . '/Route/' . $this->request->route() . '/' . $this->request->method() . '.php';
        if (!file_exists($path)) {
            throw new PiaicException('Controller not found');
        }

        /**
         * @var Controller $controller
         */
        $controller = new $controllerClass($this->request);
        return $controller;
    }
}