<?php

namespace webignition\BasilParser\Exception;

use webignition\BasilParser\Model\ExceptionContext\ExceptionContextInterface;

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

    public function applyExceptionContext(string $testName = null, string $stepName = null, string $content = null)
    {
        if (null !== $testName) {
            $this->exceptionContext->setTestName($testName);
        }

        if (null !== $stepName) {
            $this->exceptionContext->setStepName($stepName);
        }

        if (null !== $content) {
            $this->exceptionContext->setContent($content);
        }
    }
}
