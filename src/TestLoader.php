<?php

declare(strict_types=1);

namespace webignition\BasilLoader;

use webignition\BasilDataValidator\Test\TestValidator;
use webignition\BasilLoader\Exception\InvalidPageException;
use webignition\BasilLoader\Exception\InvalidTestException;
use webignition\BasilLoader\Exception\NonRetrievableDataProviderException;
use webignition\BasilLoader\Exception\NonRetrievablePageException;
use webignition\BasilLoader\Exception\NonRetrievableStepException;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModelProvider\DataSet\DataSetProvider;
use webignition\BasilModelProvider\DataSet\DataSetProviderInterface;
use webignition\BasilModelProvider\Exception\UnknownDataProviderException;
use webignition\BasilModelProvider\Exception\UnknownPageException;
use webignition\BasilModelProvider\Exception\UnknownStepException;
use webignition\BasilModelProvider\Page\PageProvider;
use webignition\BasilModelProvider\Page\PageProviderInterface;
use webignition\BasilModelProvider\Step\StepProvider;
use webignition\BasilModelProvider\Step\StepProviderInterface;
use webignition\BasilModels\Test\TestInterface;
use webignition\BasilParser\Exception\EmptyActionException;
use webignition\BasilParser\Exception\EmptyAssertionComparisonException;
use webignition\BasilParser\Exception\EmptyAssertionException;
use webignition\BasilParser\Exception\EmptyAssertionIdentifierException;
use webignition\BasilParser\Exception\EmptyAssertionValueException;
use webignition\BasilParser\Exception\EmptyInputActionValueException;
use webignition\BasilParser\Test\TestParser;
use webignition\BasilResolver\CircularStepImportException;
use webignition\BasilResolver\TestResolver;
use webignition\BasilResolver\UnknownElementException;
use webignition\BasilResolver\UnknownPageElementException;
use webignition\BasilValidationResult\InvalidResultInterface;

class TestLoader
{
    private $yamlLoader;
    private $dataSetLoader;
    private $pageLoader;
    private $stepLoader;
    private $testResolver;
    private $testParser;
    private $testValidator;

    public function __construct(
        YamlLoader $yamlLoader,
        DataSetLoader $dataSetLoader,
        PageLoader $pageLoader,
        StepLoader $stepLoader,
        TestResolver $testResolver,
        TestParser $testParser,
        TestValidator $testValidator
    ) {
        $this->yamlLoader = $yamlLoader;
        $this->dataSetLoader = $dataSetLoader;
        $this->pageLoader = $pageLoader;
        $this->stepLoader = $stepLoader;
        $this->testResolver = $testResolver;
        $this->testParser = $testParser;
        $this->testValidator = $testValidator;
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
            TestValidator::create()
        );
    }

    /**
     * @param string $path
     *
     * @return TestInterface
     *
     * @throws CircularStepImportException
     * @throws EmptyActionException
     * @throws EmptyAssertionComparisonException
     * @throws EmptyAssertionException
     * @throws EmptyAssertionIdentifierException
     * @throws EmptyAssertionValueException
     * @throws EmptyInputActionValueException
     * @throws InvalidPageException
     * @throws InvalidTestException
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

        $test = $this->testParser->parse($basePath, $path, $data);

        $imports = $test->getImports();

        $stepProvider = $this->createStepProvider($imports->getStepPaths());
        $pageProvider = $this->createPageProvider($imports->getPagePaths());
        $dataSetProvider = $this->createDataSetProvider($imports->getDataProviderPaths());

        $resolvedTest = $this->testResolver->resolve($test, $pageProvider, $stepProvider, $dataSetProvider);

        $validationResult = $this->testValidator->validate($resolvedTest);
        if ($validationResult instanceof InvalidResultInterface) {
            throw new InvalidTestException($path, $validationResult);
        }

        return $resolvedTest;
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
     * @throws InvalidPageException
     * @throws NonRetrievablePageException
     */
    private function createPageProvider(array $importPaths): PageProviderInterface
    {
        $pages = [];

        foreach ($importPaths as $importName => $importPath) {
            try {
                $pages[$importName] = $this->pageLoader->load($importName, $importPath);
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
     * @throws EmptyActionException
     * @throws EmptyAssertionComparisonException
     * @throws EmptyAssertionException
     * @throws EmptyAssertionIdentifierException
     * @throws EmptyAssertionValueException
     * @throws EmptyInputActionValueException
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
