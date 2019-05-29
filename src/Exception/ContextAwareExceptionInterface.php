<?php

namespace webignition\BasilParser\Exception;

use webignition\BasilParser\Model\ExceptionContext\ExceptionContextInterface;

interface ContextAwareExceptionInterface
{
    public function setExceptionContext(ExceptionContextInterface $exceptionContext);
    public function getExceptionContext(): ExceptionContextInterface;
}
