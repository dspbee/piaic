<?php
namespace Piaic\Main;

use Piaic\Exception\PiaicException;
use Piaic\Request\Request;
use Piaic\Response\Response;
use Piaic\Router\Router;

/**
 * Class Main
 * @package Piaic\Main
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

            if (false !== strpos($path[1], 'Route_')) {
                $path[1] = str_replace('_', '/', $path[1]);
                $package = $appDirPath . 'package/' . array_shift($path);  // Package
                $classPath = $package . '/';
                $classPath .= implode('/', $path) . '.php';
            } else {
                array_shift($path);                                        // Vendor
                $package = $appDirPath . 'package/' . array_shift($path);  // Package
                $classPath = $package . '/src/';
                $classPath .= implode('/', $path) . '.php';
            }

            if (file_exists($classPath)) {
                require_once $classPath;
            }
        });
    }

    /**
     * @param array $languages
     * @param array $packages
     * @param bool $languageInUrl             /// strictLanguage  isStrictLanguage
     * @throws PiaicException
     */
    public function echo(array $languages, array $packages, bool $languageInUrl)
    {
        $packages[] = 'Piaic';
        $request = new Request($this->appDirPath, $languages, $packages);

        if ('' == $request->language()) {
            if ($languageInUrl) {
                $response = new Response();
                $response->redirect(str_replace($request->domain(), $request->domain() . '/' . $request->defaultLanguage(), $request->fullUrl()));
            } else {
                $request->setLanguageToDefault();
            }
        }

        $routerClass = "Piaic\\Piaic\\Router\\{$request->package()}Router";
        /**
         * @var Router $routerClass
         */
        $routerClass = new $routerClass($request);
        try {
            Registry::set('__request__', $request);
            $controller = $routerClass->findController();
            if (method_exists($controller, $request->handler())) {
                $controller->invokeHandler($request->handler());
            }
            $controller->sendResponse();
        } catch (PiaicException $e) {
            if ('Controller not found' == $e->getMessage()) {
                $response = new Response();
                $response->set404($request->packageDirPath(), $request->language());
                $response->echo();
            } else {
                throw $e;
            }
        }
    }

    private $appDirPath;
}