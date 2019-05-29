<?php

namespace webignition\BasilParser\Provider\Test;

use webignition\BasilParser\Exception\NonRetrievableTestException;
use webignition\BasilParser\Model\Test\TestInterface;

interface TestProviderInterface
{
    /**
     * @param string $path
     *
     * @return TestInterface
     *
     * @throws NonRetrievableTestException
     */
    public function findTest(string $path): TestInterface;
}
