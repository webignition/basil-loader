<?php

namespace webignition\BasilParser\Exception;

class NonRetrievablePageException extends \Exception
{
    private $importName;
    private $importPath;

    public function __construct(string $importName, string $importPath, \Throwable $previous)
    {
        parent::__construct(
            'Cannot retrieve page "' . $importName . '" from "' . $importPath . '"',
            0,
            $previous
        );

        $this->importName = $importName;
        $this->importPath = $importPath;
    }

    public function getImportName(): string
    {
        return $this->importName;
    }

    public function getImportPath(): string
    {
        return $this->importPath;
    }
}
