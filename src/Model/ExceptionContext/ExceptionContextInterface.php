<?php

namespace webignition\BasilParser\Model\ExceptionContext;

interface ExceptionContextInterface
{
    public function setTestName(string $testName);
    public function getTestName(): ?string;
    public function setStepName(string $stepName);
    public function getStepName(): ?string;
    public function setContent(string $content);
    public function getContent(): ?string;
}
