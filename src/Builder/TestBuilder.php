<?php

namespace webignition\BasilParser\Builder;

use webignition\BasilModel\Test\TestInterface;
use webignition\BasilDataStructure\Test\Test as TestData;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilModelFactory\Test\TestFactory;
use webignition\BasilModelProvider\DataSet\DataSetProviderInterface;
use webignition\BasilModelProvider\Exception\UnknownDataProviderException;
use webignition\BasilModelProvider\Exception\UnknownPageException;
use webignition\BasilModelProvider\Exception\UnknownStepException;
use webignition\BasilParser\Exception\CircularStepImportException;
use webignition\BasilParser\Exception\UnknownElementException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Step\StepProviderInterface;
use webignition\BasilParser\Resolver\Test\TestResolver;

class TestBuilder
{
    private $testFactory;
    private $testResolver;

    public function __construct(TestFactory $testFactory, TestResolver $testResolver)
    {
        $this->testFactory = $testFactory;
        $this->testResolver = $testResolver;
    }

    public static function createBuilder(): TestBuilder
    {
        return new TestBuilder(
            TestFactory::createFactory(),
            TestResolver::createResolver()
        );
    }

    /**
     * @param TestData $testData
     * @param PageProviderInterface $pageProvider
     * @param StepProviderInterface $stepProvider
     * @param DataSetProviderInterface $dataSetProvider
     *
     * @return TestInterface
     *
     * @throws CircularStepImportException
     * @throws MalformedPageElementReferenceException
     * @throws UnknownDataProviderException
     * @throws UnknownElementException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws UnknownStepException
     */
    public function build(
        TestData $testData,
        PageProviderInterface $pageProvider,
        StepProviderInterface $stepProvider,
        DataSetProviderInterface $dataSetProvider
    ): TestInterface {
        $unresolvedTest = $this->testFactory->createFromTestData($testData->getPath(), $testData);

        return $this->testResolver->resolve($unresolvedTest, $pageProvider, $stepProvider, $dataSetProvider);
    }
}
