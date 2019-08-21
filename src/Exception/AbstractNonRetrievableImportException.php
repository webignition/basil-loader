<?php

namespace webignition\BasilLoader\Exception;

use webignition\BasilContextAwareException\ContextAwareExceptionInterface;
use webignition\BasilContextAwareException\ContextAwareExceptionTrait;
use webignition\BasilContextAwareException\ExceptionContext\ExceptionContext;

abstract class AbstractNonRetrievableImportException extends \Exception implements ContextAwareExceptionInterface
{
    use ContextAwareExceptionTrait;

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
}
