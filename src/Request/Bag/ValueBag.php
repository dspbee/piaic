<?php
namespace Piaic\Request\Bag;

/**
 * Class ValueBag
 * @package Piaic\Request\Bag
 */
class ValueBag
{
    private $bag;

    /**
     * @param array $bag
     */
    public function __construct(array $bag = [])
    {
        $this->bag = $bag;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasKey(string $key): bool
    {
        return array_key_exists($key, $this->bag);
    }

    /**
     * @param string $value
     * @return bool
     */
    public function hasValue(string $value): bool
    {
        return in_array($value, $this->bag);
    }

    /**
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->bag);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->bag);
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed|string|null
     */
    public function fetch(string $key, $default = null)
    {
        $val = $this->bag[$key] ?? $default;
        if (!is_array($val)) {
            return trim($val);
        } else {
            return $val;
        }
    }

    /**
     * @param string $key
     * @param int $default
     * @return int
     */
    public function fetchInt(string $key, int $default = 0): int
    {
        return intval($this->fetch($key, $default));
    }

    /**
     * @param string $key
     * @param float $default
     * @param int $precision
     * @return float
     */
    public function fetchFloat(string $key, float $default = 0.0, int $precision = 2): float
    {
        return round(floatval($this->fetch($key, $default)), $precision);
    }
}