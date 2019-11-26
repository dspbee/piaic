<?php
namespace Piaic\Application;

/**
 * Interface InputData
 * @package Piaic\Application
 */
interface InputData
{
    /**
     * @param $value
     * @return mixed
     */
    public function validate($value);
}