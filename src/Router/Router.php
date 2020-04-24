<?php
namespace Piaic\Router;

use Piaic\Controller\Controller;

/**
 * Interface Router
 * @package Piaic\Router
 */
interface Router
{
    /**
     * @return Controller
     */
    public function findController(): Controller;
}