<?php

declare(strict_types=1);

namespace Core;

final class Config
{
    private array $items = [];

    public function __construct(string $configPath)
    {
        $this->loadConfigFiles($configPath);
    }

    private function loadConfigFiles(string $path): void
    {
        foreach (glob($path . '/*.php') as $file) {
            $key = basename($file, '.php');
            $this->items[$key] = require $file;
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $array = $this->items;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }

        return $array;
    }
}
