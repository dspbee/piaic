<?php
namespace Piaic\Application;

/**
 * Interface Router
 * @package Piaic\Application
 */
interface Router
{
    /**
     * Router constructor.
     * @param Request $request
     */
    public function __construct(Request $request);

    /**
     * @return Controller
     */
    public function findController(): Controller;
}