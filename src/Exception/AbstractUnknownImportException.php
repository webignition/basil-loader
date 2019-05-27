<?php

namespace webignition\BasilParser\Exception;

abstract class AbstractUnknownImportException extends \Exception
{
    private $importName;

    public function __construct(string $importName, string $message)
    {
        parent::__construct($message);

        $this->importName = $importName;
    }

    public function getImportName(): string
    {
        return $this->importName;
    }
}
