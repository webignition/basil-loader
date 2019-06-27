<?php

namespace webignition\BasilParser\DataStructure\Test;

use webignition\BasilParser\DataStructure\AbstractDataStructure;

class Imports extends AbstractDataStructure
{
    const KEY_STEPS = 'steps';
    const KEY_PAGES = 'pages';
    const KEY_DATA_PROVIDERS = 'data_providers';

    const CURRENT_DIRECTORY = '.';
    const PREVIOUS_DIRECTORY = '..';

    private $basePath = '';

    public function __construct(array $data, string $basePath)
    {
        parent::__construct($data);

        $this->basePath = $basePath;
    }

    public function getStepPaths(): array
    {
        return $this->resolvePaths($this->getArray(self::KEY_STEPS));
    }

    public function getPagePaths(): array
    {
        return $this->resolvePaths($this->getArray(self::KEY_PAGES));
    }

    public function getDataProviderPaths(): array
    {
        return $this->resolvePaths($this->getArray(self::KEY_DATA_PROVIDERS));
    }

    private function resolvePaths(array $paths): array
    {
        if (empty($paths)) {
            return $paths;
        }

        if (empty($this->basePath)) {
            return $paths;
        }

        foreach ($paths as $pathIndex => $path) {
            if ($this->isRelativePath($path)) {
                $paths[$pathIndex] = $this->resolveRelativePath($this->basePath . $path);
            }
        }

        return $paths;
    }

    private function isRelativePath(string $path): bool
    {
        $parts = explode(DIRECTORY_SEPARATOR, $path);

        return count($parts) > 0 && (self::CURRENT_DIRECTORY === $parts[0] || self::PREVIOUS_DIRECTORY === $parts[0]);
    }

    private function resolveRelativePath(string $path): string
    {
        $resolvedPathParts = [];
        $parts = explode(DIRECTORY_SEPARATOR, $path);

        foreach ($parts as $part) {
            $part = trim($part);

            if ('' === $part || self::CURRENT_DIRECTORY === $part) {
                continue;
            }

            if (self::PREVIOUS_DIRECTORY !== $part) {
                array_push($resolvedPathParts, $part);
            } elseif (count($resolvedPathParts) > 0) {
                array_pop($resolvedPathParts);
            }
        }

        return DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $resolvedPathParts);
    }
}
