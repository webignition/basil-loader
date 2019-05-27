<?php

namespace webignition\BasilParser\Exception;

abstract class AbstractNonRetrievableImportException extends \Exception
{
    private $importName;
    private $importPath;

    public function __construct(string $importName, string $importPath, string $message, \Throwable $previous)
    {
        parent::__construct($message, 0, $previous);

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
