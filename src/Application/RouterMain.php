<?php
namespace Piaic\Application;

/**
 * Class RouterMain
 * @package Piaic\Application
 */
class RouterMain implements Router
{
    /**
     * RouterMain constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->route = [];
    }

    /**
     * @return Controller
     */
    public function findController(): Controller
    {
        $controllerClass = "\\Vendor\\{$this->request->package}\\Route\\{$this->request->route}\\{$this->request->method}";
        /**
         * @var Controller $controller
         */
        $controller = new $controllerClass($this->request, $this->route);
        return $controller;
    }

    protected $request;
    protected $route;
}