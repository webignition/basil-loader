<?php

declare(strict_types=1);

namespace webignition\BasilLoader;

use webignition\BasilLoader\Exception\NonRetrievableDataProviderException;
use webignition\BasilLoader\Exception\NonRetrievablePageException;
use webignition\BasilLoader\Exception\NonRetrievableStepException;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModel\Test\TestInterface;
use webignition\BasilModelFactory\Exception\EmptyAssertionStringException;
use webignition\BasilModelFactory\Exception\InvalidActionTypeException;
use webignition\BasilModelFactory\Exception\InvalidIdentifierStringException;
use webignition\BasilModelFactory\Exception\MissingComparisonException;
use webignition\BasilModelFactory\Exception\MissingValueException;
use webignition\BasilModelFactory\InvalidPageElementIdentifierException;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilModelFactory\Test\TestFactory;
use webignition\BasilModelProvider\DataSet\DataSetProvider;
use webignition\BasilModelProvider\DataSet\DataSetProviderInterface;
use webignition\BasilModelProvider\Exception\UnknownDataProviderException;
use webignition\BasilModelProvider\Exception\UnknownPageException;
use webignition\BasilModelProvider\Exception\UnknownStepException;
use webignition\BasilModelProvider\Page\PageProvider;
use webignition\BasilModelProvider\Page\PageProviderInterface;
use webignition\BasilModelProvider\Step\StepProvider;
use webignition\BasilModelProvider\Step\StepProviderInterface;
use webignition\BasilModelResolver\CircularStepImportException;
use webignition\BasilModelResolver\TestResolver;
use webignition\BasilModelResolver\UnknownElementException;
use webignition\BasilModelResolver\UnknownPageElementException;
use webignition\BasilParser\Test\TestParser;

class TestLoader
{
    private $yamlLoader;
    private $dataSetLoader;
    private $pageLoader;
    private $stepLoader;
    private $testResolver;
    private $testParser;
    private $testFactory;

    public function __construct(
        YamlLoader $yamlLoader,
        DataSetLoader $dataSetLoader,
        PageLoader $pageLoader,
        StepLoader $stepLoader,
        TestResolver $testResolver,
        TestParser $testParser,
        TestFactory $testFactory
    ) {
        $this->yamlLoader = $yamlLoader;
        $this->dataSetLoader = $dataSetLoader;
        $this->pageLoader = $pageLoader;
        $this->stepLoader = $stepLoader;
        $this->testResolver = $testResolver;
        $this->testParser = $testParser;
        $this->testFactory = $testFactory;
    }

    public static function createLoader(): TestLoader
    {
        return new TestLoader(
            YamlLoader::createLoader(),
            DataSetLoader::createLoader(),
            PageLoader::createLoader(),
            StepLoader::createLoader(),
            TestResolver::createResolver(),
            TestParser::create(),
            TestFactory::createFactory()
        );
    }

    /**
     * @param string $path
     *
     * @return TestInterface
     *
     * @throws CircularStepImportException
     * @throws EmptyAssertionStringException
     * @throws InvalidActionTypeException
     * @throws InvalidIdentifierStringException
     * @throws InvalidPageElementIdentifierException
     * @throws MalformedPageElementReferenceException
     * @throws MissingComparisonException
     * @throws MissingValueException
     * @throws NonRetrievableDataProviderException
     * @throws NonRetrievablePageException
     * @throws NonRetrievableStepException
     * @throws UnknownDataProviderException
     * @throws UnknownElementException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws UnknownStepException
     * @throws YamlLoaderException
     */
    public function load(string $path): TestInterface
    {
        $basePath = dirname($path) . '/';
        $data = $this->yamlLoader->loadArray($path);

        $testData = $this->testParser->parse($basePath, $path, $data);

        $imports = $testData->getImports();

        $stepProvider = $this->createStepProvider($imports->getStepPaths());
        $pageProvider = $this->createPageProvider($imports->getPagePaths());
        $dataSetProvider = $this->createDataSetProvider($imports->getDataProviderPaths());

        $unresolvedTest = $this->testFactory->createFromTestData($testData->getPath(), $testData);

        return $this->testResolver->resolve($unresolvedTest, $pageProvider, $stepProvider, $dataSetProvider);
    }

    /**
     * @param array $importPaths
     *
     * @return DataSetProviderInterface
     *
     * @throws NonRetrievableDataProviderException
     */
    private function createDataSetProvider(array $importPaths): DataSetProviderInterface
    {
        $dataSetCollections = [];

        foreach ($importPaths as $importName => $importPath) {
            try {
                $dataSetCollections[$importName] = $this->dataSetLoader->load($importPath);
            } catch (YamlLoaderException $yamlLoaderException) {
                throw new NonRetrievableDataProviderException($importName, $importPath, $yamlLoaderException);
            }
        }

        return new DataSetProvider($dataSetCollections);
    }

    /**
     * @param array $importPaths
     *
     * @return PageProviderInterface
     *
     * @throws InvalidPageElementIdentifierException
     * @throws NonRetrievablePageException
     */
    private function createPageProvider(array $importPaths): PageProviderInterface
    {
        $pages = [];

        foreach ($importPaths as $importName => $importPath) {
            try {
                $pages[$importName] = $this->pageLoader->load($importPath);
            } catch (YamlLoaderException $yamlLoaderException) {
                throw new NonRetrievablePageException($importName, $importPath, $yamlLoaderException);
            }
        }

        return new PageProvider($pages);
    }

    /**
     * @param array $importPaths
     *
     * @return StepProviderInterface
     *
     * @throws EmptyAssertionStringException
     * @throws InvalidActionTypeException
     * @throws InvalidIdentifierStringException
     * @throws MalformedPageElementReferenceException
     * @throws MissingComparisonException
     * @throws MissingValueException
     * @throws NonRetrievableStepException
     */
    private function createStepProvider(array $importPaths): StepProviderInterface
    {
        $steps = [];

        foreach ($importPaths as $importName => $importPath) {
            try {
                $steps[$importName] = $this->stepLoader->load($importPath);
            } catch (YamlLoaderException $yamlLoaderException) {
                throw new NonRetrievableStepException($importName, $importPath, $yamlLoaderException);
            }
        }

        return new StepProvider($steps);
    }
}
