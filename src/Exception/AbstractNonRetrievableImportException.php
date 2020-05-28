<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Exception;

use webignition\BasilContextAwareException\ContextAwareExceptionInterface;
use webignition\BasilContextAwareException\ContextAwareExceptionTrait;
use webignition\BasilContextAwareException\ExceptionContext\ExceptionContext;
use webignition\BasilContextAwareException\ExceptionContext\ExceptionContextInterface;

abstract class AbstractNonRetrievableImportException extends \Exception implements ContextAwareExceptionInterface
{
    use ContextAwareExceptionTrait;

    private string $importName;
    private string $importPath;
    private ExceptionContextInterface $exceptionContext;

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
