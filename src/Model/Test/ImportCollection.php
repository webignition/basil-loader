<?php

namespace webignition\BasilParser\Model\Test;

class ImportCollection implements ImportCollectionInterface
{
    private $pageImportPaths = [];
    private $stepImportPaths = [];

    public function __construct(array $pageImportPaths, array $stepImportPaths)
    {
        foreach ($pageImportPaths as $importName => $importPath) {
            if (is_string($importPath)) {
                $this->pageImportPaths[$importName] = $importPath;
            }
        }

        foreach ($stepImportPaths as $importName => $importPath) {
            if (is_string($importPath)) {
                $this->stepImportPaths[$importName] = $importPath;
            }
        }
    }


    public function getPageImportPath(string $name): ?string
    {
        return $this->getImportPath($this->pageImportPaths, $name);
    }

    public function getStepImportPath(string $name): ?string
    {
        return $this->getImportPath($this->stepImportPaths, $name);
    }

    private function getImportPath(array $collection, string $name): ?string
    {
        return $collection[$name] ?? null;
    }
}
