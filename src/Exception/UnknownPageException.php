<?php

namespace webignition\BasilParser\Exception;

class UnknownPageException extends \Exception
{
    private $importName;

    public function __construct(string $importName)
    {
        parent::__construct('Unknown page "' . $importName . '"');

        $this->importName = $importName;
    }

    public function getImportName(): string
    {
        return $this->importName;
    }
}
