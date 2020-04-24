<?php
namespace Piaic\Controller;

/**
 * Interface Controller
 * @package Piaic\Application
 */
interface Controller
{
    public function invokeHandler(string $handlerName);

    public function sendResponse();
}