<?php

declare(strict_types=1);

namespace webignition\BasilLoader;

use webignition\BasilDataValidator\Test\TestValidator;
use webignition\BasilLoader\Exception\InvalidPageException;
use webignition\BasilLoader\Exception\InvalidTestException;
use webignition\BasilLoader\Exception\NonRetrievableDataProviderException;
use webignition\BasilLoader\Exception\NonRetrievablePageException;
use webignition\BasilLoader\Exception\NonRetrievableStepException;
use webignition\BasilLoader\Exception\ParseException;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModelProvider\DataSet\DataSetProvider;
use webignition\BasilModelProvider\Exception\UnknownItemException;
use webignition\BasilModelProvider\Page\PageProvider;
use webignition\BasilModelProvider\ProviderInterface;
use webignition\BasilModelProvider\Step\StepProvider;
use webignition\BasilModels\Test\TestInterface;
use webignition\BasilParser\Exception\UnparseableStepException;
use webignition\BasilParser\Exception\UnparseableTestException;
use webignition\BasilParser\Test\ImportsParser;
use webignition\BasilParser\Test\TestParser;
use webignition\BasilResolver\CircularStepImportException;
use webignition\BasilResolver\TestResolver;
use webignition\BasilResolver\UnknownElementException;
use webignition\BasilResolver\UnknownPageElementException;
use webignition\BasilValidationResult\InvalidResultInterface;

class TestLoader
{
    private const DATA_KEY_IMPORTS = 'imports';

    private $yamlLoader;
    private $dataSetLoader;
    private $pageLoader;
    private $stepLoader;
    private $testResolver;
    private $testParser;
    private $testValidator;
    private $importsParser;

    public function __construct(
        YamlLoader $yamlLoader,
        DataSetLoader $dataSetLoader,
        PageLoader $pageLoader,
        StepLoader $stepLoader,
        TestResolver $testResolver,
        TestParser $testParser,
        TestValidator $testValidator,
        ImportsParser $importsParser
    ) {
        $this->yamlLoader = $yamlLoader;
        $this->dataSetLoader = $dataSetLoader;
        $this->pageLoader = $pageLoader;
        $this->stepLoader = $stepLoader;
        $this->testResolver = $testResolver;
        $this->testParser = $testParser;
        $this->testValidator = $testValidator;
        $this->importsParser = $importsParser;
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
            TestValidator::create(),
            ImportsParser::create()
        );
    }

    /**
     * @param string $path
     *
     * @return TestInterface
     *
     * @throws CircularStepImportException
     * @throws InvalidPageException
     * @throws InvalidTestException
     * @throws NonRetrievableDataProviderException
     * @throws NonRetrievablePageException
     * @throws NonRetrievableStepException
     * @throws ParseException
     * @throws UnknownElementException
     * @throws UnknownItemException
     * @throws UnknownPageElementException
     * @throws YamlLoaderException
     */
    public function load(string $path): TestInterface
    {
        $basePath = dirname($path) . '/';
        $data = $this->yamlLoader->loadArray($path);

        try {
            $test = $this->testParser->parse($data);
        } catch (UnparseableTestException $unparseableTestException) {
            throw new ParseException($path, $unparseableTestException);
        }

        $test = $test->withPath($path);

        $imports = $this->importsParser->parse($basePath, $data[self::DATA_KEY_IMPORTS] ?? []);

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
     * @param array<string, string> $importPaths
     *
     * @return ProviderInterface
     *
     * @throws NonRetrievableDataProviderException
     */
    private function createDataSetProvider(array $importPaths): ProviderInterface
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
     * @param array<string, string> $importPaths
     *
     * @return ProviderInterface
     *
     * @throws InvalidPageException
     * @throws NonRetrievablePageException
     */
    private function createPageProvider(array $importPaths): ProviderInterface
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
     * @param array<string, string> $importPaths
     *
     * @return ProviderInterface
     *
     * @throws NonRetrievableStepException
     * @throws ParseException
     */
    private function createStepProvider(array $importPaths): ProviderInterface
    {
        $steps = [];

        foreach ($importPaths as $importName => $importPath) {
            try {
                $steps[$importName] = $this->stepLoader->load($importPath);
            } catch (YamlLoaderException $yamlLoaderException) {
                throw new NonRetrievableStepException($importName, $importPath, $yamlLoaderException);
            } catch (UnparseableStepException $unparseableStepException) {
                throw new ParseException($importPath, $unparseableStepException);
            }
        }

        return new StepProvider($steps);
    }
}
