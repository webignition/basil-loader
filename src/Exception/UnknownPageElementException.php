<?php

namespace webignition\BasilParser\Exception;

class UnknownPageElementException extends \Exception
{
    private $importName;
    private $elementName;

    public function __construct(string $importName, string $elementName)
    {
        parent::__construct('Unknown page element "' . $elementName . '" in page "' . $importName . '"');

        $this->importName = $importName;
        $this->elementName = $elementName;
    }

    public function getImportName(): string
    {
        return $this->importName;
    }

    public function getElementName(): string
    {
        return $this->elementName;
    }
}
