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

    public function getStepPaths(string $basePath = ''): array
    {
        return $this->resolvePaths($this->getArray(self::KEY_STEPS), $basePath);
    }

    public function getPagePaths(string $basePath = ''): array
    {
        return $this->resolvePaths($this->getArray(self::KEY_PAGES), $basePath);
    }

    public function getDataProviderPaths(string $basePath = ''): array
    {
        return $this->resolvePaths($this->getArray(self::KEY_DATA_PROVIDERS), $basePath);
    }

    private function resolvePaths(array $paths, string $basePath): array
    {
        if (empty($paths)) {
            return $paths;
        }

        if (empty($basePath)) {
            return $paths;
        }

        foreach ($paths as $pathIndex => $path) {
            if ($this->isRelativePath($path)) {
                $paths[$pathIndex] = $this->resolveRelativePath($basePath . $path);
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
