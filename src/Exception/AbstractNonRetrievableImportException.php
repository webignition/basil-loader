<?php

namespace webignition\BasilParser\Exception;

use webignition\BasilParser\Model\ExceptionContext\ExceptionContext;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContextInterface;

abstract class AbstractNonRetrievableImportException extends \Exception implements ContextAwareExceptionInterface
{
    private $importName;
    private $importPath;
    private $exceptionContext;

    public function __construct(string $importName, string $importPath, string $message, \Throwable $previous)
    {
        parent::__construct($message, 0, $previous);

        $this->importName = $importName;
        $this->importPath = $importPath;
        $this->exceptionContext = new ExceptionContext();
    }

    public function getImportName(): string
    {
        return $this->importName;
    }

    public function getImportPath(): string
    {
        return $this->importPath;
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
