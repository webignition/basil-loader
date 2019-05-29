<?php

namespace webignition\BasilParser\Model\TestSuite;

use webignition\BasilParser\Model\Test\TestInterface;

interface TestSuiteInterface
{
    /**
     * @return TestInterface[]
     */
    public function getTests(): array;
}
