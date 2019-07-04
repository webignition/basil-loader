<?php

namespace webignition\BasilParser\Provider\Test;

use webignition\BasilModel\Test\TestInterface;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\NonRetrievableTestException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Exception\UnknownTestException;

interface TestProviderInterface
{
    /**
     * @param string $path
     *
     * @return TestInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableDataProviderException
     * @throws NonRetrievableStepException
     * @throws NonRetrievableTestException
     * @throws UnknownDataProviderException
     * @throws UnknownStepException
     * @throws UnknownTestException
     */
    public function findTest(string $path): TestInterface;

    /**
     * @param string[] $paths
     *
     * @return TestInterface[]
     *
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableDataProviderException
     * @throws NonRetrievableStepException
     * @throws NonRetrievableTestException
     * @throws UnknownDataProviderException
     * @throws UnknownStepException
     * @throws UnknownTestException
     */
    public function findCollection(array $paths): array;
}
