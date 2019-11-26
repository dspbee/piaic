<?php
namespace Piaic\Application;

/**
 * Interface OutputData
 * @package Piaic\Application
 */
interface OutputData
{
    /**
     * @param $value
     * @return mixed
     */
    public function transform($value);
}