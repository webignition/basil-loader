<?php

namespace webignition\BasilParser\Exception;

use webignition\BasilParser\Model\ExceptionContext\ExceptionContext;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContextInterface;

abstract class AbstractUnknownImportException extends \Exception implements ContextAwareExceptionInterface
{
    private $importName;
    private $exceptionContext;

    public function __construct(string $importName, string $message)
    {
        parent::__construct($message);

        $this->importName = $importName;
        $this->exceptionContext = new ExceptionContext();
    }

    public function getImportName(): string
    {
        return $this->importName;
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
