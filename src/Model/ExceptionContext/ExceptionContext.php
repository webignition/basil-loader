<?php

namespace webignition\BasilParser\Model\ExceptionContext;

class ExceptionContext implements ExceptionContextInterface
{
    private $testName;
    private $stepName;
    private $content;

    public function setTestName(string $testName)
    {
        $this->testName = $testName;
    }

    public function getTestName(): ?string
    {
        return $this->testName;
    }

    public function setStepName(string $stepName)
    {
        $this->stepName = $stepName;
    }

    public function getStepName(): ?string
    {
        return $this->stepName;
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }
}
