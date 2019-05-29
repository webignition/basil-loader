<?php

namespace webignition\BasilParser\Exception;

use webignition\BasilParser\Model\ExceptionContext\ExceptionContext;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContextInterface;

class UnknownPageElementException extends \Exception implements ContextAwareExceptionInterface
{
    private $importName;
    private $elementName;
    private $exceptionContext;

    public function __construct(string $importName, string $elementName)
    {
        parent::__construct('Unknown page element "' . $elementName . '" in page "' . $importName . '"');

        $this->importName = $importName;
        $this->elementName = $elementName;
        $this->exceptionContext = new ExceptionContext();
    }

    public function getImportName(): string
    {
        return $this->importName;
    }

    public function getElementName(): string
    {
        return $this->elementName;
    }

    public function setExceptionContext(ExceptionContextInterface $exceptionContext)
    {
        $this->exceptionContext = $exceptionContext;
    }

    public function getExceptionContext(): ExceptionContextInterface
    {
        return $this->exceptionContext;
    }
}
