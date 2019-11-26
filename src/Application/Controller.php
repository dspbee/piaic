<?php
namespace Piaic\Application;

/**
 * Interface Controller
 * @package Piaic\Application
 */
interface Controller
{
    public function invokeHandler(string $handlerName);

    public function getResponse(): Response;

    public function setView();

    public function setViewError();

    public function setViewSuccess();

    public function setViewInfo();

    public function setJson();
}