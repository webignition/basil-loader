<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Exception;

use webignition\BasilContextAwareException\ContextAwareExceptionInterface;
use webignition\BasilContextAwareException\ContextAwareExceptionTrait;
use webignition\BasilContextAwareException\ExceptionContext\ExceptionContext;

abstract class AbstractUnknownImportException extends \Exception implements ContextAwareExceptionInterface
{
    use ContextAwareExceptionTrait;

    private string $importName;

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
}
