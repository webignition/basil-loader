<?php

namespace webignition\BasilParser\Builder;

use webignition\BasilModel\Test\TestInterface;
use webignition\BasilParser\DataStructure\Test\Test as TestData;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Factory\Test\TestFactory;
use webignition\BasilParser\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
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

    /**
     * @param TestData $testData
     * @param PageProviderInterface $pageProvider
     * @param StepProviderInterface $stepProvider
     * @param DataSetProviderInterface $dataSetProvider
     *
     * @return TestInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableDataProviderException
     * @throws NonRetrievablePageException
     * @throws NonRetrievableStepException
     * @throws UnknownDataProviderException
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
