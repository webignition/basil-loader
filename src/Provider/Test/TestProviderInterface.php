<?php

namespace webignition\BasilParser\Provider\Test;

use webignition\BasilModel\Test\TestInterface;
use webignition\BasilParser\Exception\CircularStepImportException;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\NonRetrievableTestException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Exception\UnknownTestException;

interface TestProviderInterface
{
    /**
     * @param string $path
     *
     * @return TestInterface
     *
     * @throws CircularStepImportException
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableDataProviderException
     * @throws NonRetrievablePageException
     * @throws NonRetrievableStepException
     * @throws NonRetrievableTestException
     * @throws UnknownDataProviderException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws UnknownStepException
     * @throws UnknownTestException
     */
    public function findTest(string $path): TestInterface;

    /**
     * @param string[] $paths
     *
     * @return TestInterface[]
     *
     * @throws CircularStepImportException
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableDataProviderException
     * @throws NonRetrievablePageException
     * @throws NonRetrievableStepException
     * @throws NonRetrievableTestException
     * @throws UnknownDataProviderException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws UnknownStepException
     * @throws UnknownTestException
     */
    public function findCollection(array $paths): array;
}
