<?php

namespace webignition\BasilParser\Loader;

use webignition\BasilDataStructure\PathResolver;
use webignition\BasilModel\Test\TestInterface;
use webignition\BasilParser\Builder\TestBuilder;
use webignition\BasilDataStructure\Test\Test as TestData;
use webignition\BasilParser\Exception\CircularStepImportException;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Exception\YamlLoaderException;
use webignition\BasilParser\Provider\DataSet\Factory as DataSetProviderFactory;
use webignition\BasilParser\Provider\Page\Factory as PageProviderFactory;
use webignition\BasilParser\Provider\Step\Factory as StepProviderFactory;

class TestLoader
{
    private $yamlLoader;
    private $testBuilder;
    private $pathResolver;
    private $stepProviderFactory;
    private $pageProviderFactory;
    private $dataSetProviderFactory;

    public function __construct(
        YamlLoader $yamlLoader,
        TestBuilder $testBuilder,
        PathResolver $pathResolver,
        StepProviderFactory $stepProviderFactory,
        PageProviderFactory $pageProviderFactory,
        DataSetProviderFactory $dataSetProviderFactory
    ) {
        $this->yamlLoader = $yamlLoader;
        $this->testBuilder = $testBuilder;
        $this->pathResolver = $pathResolver;
        $this->stepProviderFactory = $stepProviderFactory;
        $this->pageProviderFactory = $pageProviderFactory;
        $this->dataSetProviderFactory = $dataSetProviderFactory;
    }

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
     * @throws UnknownDataProviderException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws UnknownStepException
     * @throws YamlLoaderException
     */
    public function load(string $path): TestInterface
    {
        $data = $this->yamlLoader->loadArray($path);
        $testData = new TestData($this->pathResolver, $data, $path);

        $imports = $testData->getImports();

        $stepProvider = $this->stepProviderFactory->createDeferredStepProvider($imports->getStepPaths());
        $pageProvider = $this->pageProviderFactory->createDeferredPageProvider($imports->getPagePaths());
        $dataSetProvider = $this->dataSetProviderFactory->createDeferredDataSetProvider(
            $imports->getDataProviderPaths()
        );

        return $this->testBuilder->build($testData, $pageProvider, $stepProvider, $dataSetProvider);
    }
}
