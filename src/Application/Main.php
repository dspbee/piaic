<?php
namespace Piaic\Application;


/**
 * Class Main
 * @package Piaic\Application
 */
class Main
{
    /**
     * Main constructor.
     * @param string $appDirPath
     */
    public function __construct(string $appDirPath)
    {
        $appDirPath = rtrim($appDirPath, '/') . '/';
        $this->appDirPath = $appDirPath;

        /**
         * Register autoload to app/package/$package/src dir's and route.
         */
        spl_autoload_register(function ($path) use ($appDirPath) {
            $path = explode('\\', $path);
            array_shift($path);                                        // Vendor
            $package = $appDirPath . 'package/' . array_shift($path);  // Package
            $classPath = $package . '/';
            if ('Route' != $path[0]) {
                $classPath .= 'src/';
            }
            $classPath .= implode('/', $path) . '.php';
            if (file_exists($classPath)) {
                require_once $classPath;
            }
        });
    }

    /**
     * @param array $languages
     * @param array $packages
     * @throws PiaicException
     */
    public function echo(array $languages, array $packages)
    {
        $packages[] = 'Piaic';
        $request = new Request($languages, $packages);
        $request->appDirPath = $this->appDirPath;

        $routerClass = "Piaic\\System\\Router\\{$request->package}Router";
        /**
         * @var Router $routerClass
         */
        $routerClass = new $routerClass($request);

        $controller = $routerClass->findController();
        if (method_exists($controller, $request->handler)) {
            $controller->invokeHandler($request->handler);
        }

        $response = $controller->getResponse();
        if ($response->isNotFound()) {
            $response->set404($request->appDirPath . 'package/' . $request->package, $request->language);
        }
        $response->echo();
    }

    private $appDirPath;
}