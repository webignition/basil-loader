<?php

namespace webignition\BasilParser\Exception;

use webignition\BasilModel\ExceptionContext\ExceptionContextInterface;

trait ContextAwareExceptionTrait
{
    /**
     * @var ExceptionContextInterface
     */
    private $exceptionContext;

    public function getExceptionContext(): ExceptionContextInterface
    {
        return $this->exceptionContext;
    }

    public function applyExceptionContext(array $values)
    {
        $this->exceptionContext->apply($values);
    }
}
