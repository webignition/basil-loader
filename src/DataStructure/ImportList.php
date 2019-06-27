<?php

namespace webignition\BasilParser\DataStructure;

class ImportList extends AbstractDataStructure
{
    const CURRENT_DIRECTORY = '.';
    const PREVIOUS_DIRECTORY = '..';

    private $basePath = '';

    public function __construct(array $data, string $basePath)
    {
        parent::__construct($data);

        $this->basePath = $basePath;
    }

    public function getPaths(): array
    {
        return $this->resolvePaths($this->data);
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
