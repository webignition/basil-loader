<?php

namespace webignition\BasilParser\Resolver;

use webignition\BasilModel\TestSuite\TestSuite;
use webignition\BasilModel\TestSuite\TestSuiteInterface;
use webignition\BasilModelProvider\Exception\UnknownDataProviderException;
use webignition\BasilModelProvider\Exception\UnknownPageException;
use webignition\BasilParser\Exception\CircularStepImportException;
use webignition\BasilParser\Exception\UnknownElementException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Step\StepProviderInterface;
use webignition\BasilParser\Resolver\Test\TestResolver;

class TestSuiteResolver
{
    private $testResolver;

    public function __construct(TestResolver $testResolver)
    {
        $this->testResolver = $testResolver;
    }

    public static function createResolver(): TestSuiteResolver
    {
        return new TestSuiteResolver(
            TestResolver::createResolver()
        );
    }

    /**
     * @param TestSuiteInterface $testSuite
     * @param PageProviderInterface $pageProvider
     * @param StepProviderInterface $stepProvider
     * @param DataSetProviderInterface $dataSetProvider
     *
     * @return TestSuiteInterface
     *
     * @throws CircularStepImportException
     * @throws UnknownDataProviderException
     * @throws UnknownElementException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws UnknownStepException
     */
    public function resolve(
        TestSuiteInterface $testSuite,
        PageProviderInterface $pageProvider,
        StepProviderInterface $stepProvider,
        DataSetProviderInterface $dataSetProvider
    ): TestSuiteInterface {
        $resolvedTests = [];

        foreach ($testSuite->getTests() as $test) {
            $resolvedTests[] = $this->testResolver->resolve($test, $pageProvider, $stepProvider, $dataSetProvider);
        }

        return new TestSuite($testSuite->getName(), $resolvedTests);
    }
}
