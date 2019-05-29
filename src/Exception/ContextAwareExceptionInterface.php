<?php

namespace webignition\BasilParser\Exception;

use webignition\BasilParser\Model\ExceptionContext\ExceptionContextInterface;

interface ContextAwareExceptionInterface
{
    public function getExceptionContext(): ExceptionContextInterface;
    public function applyExceptionContext(array $values);
}
