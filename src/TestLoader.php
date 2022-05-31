<?php

declare(strict_types=1);

namespace webignition\BasilLoader;

use webignition\BasilDataValidator\Test\TestValidator;
use webignition\BasilLoader\Exception\EmptyTestException;
use webignition\BasilLoader\Exception\InvalidPageException;
use webignition\BasilLoader\Exception\InvalidTestException;
use webignition\BasilLoader\Exception\NonRetrievableImportException;
use webignition\BasilLoader\Exception\ParseException;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModelProvider\DataSet\DataSetProvider;
use webignition\BasilModelProvider\DataSet\DataSetProviderInterface;
use webignition\BasilModelProvider\Exception\UnknownItemException;
use webignition\BasilModelProvider\Page\PageProvider;
use webignition\BasilModelProvider\Page\PageProviderInterface;
use webignition\BasilModelProvider\Step\StepProvider;
use webignition\BasilModelProvider\Step\StepProviderInterface;
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

    public function __construct(
        private YamlLoader $yamlLoader,
        private DataSetLoader $dataSetLoader,
        private PageLoader $pageLoader,
        private StepLoader $stepLoader,
        private TestResolver $testResolver,
        private TestParser $testParser,
        private TestValidator $testValidator,
        private ImportsParser $importsParser
    ) {
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
     * @throws CircularStepImportException
     * @throws EmptyTestException
     * @throws InvalidPageException
     * @throws InvalidTestException
     * @throws NonRetrievableImportException
     * @throws ParseException
     * @throws UnknownElementException
     * @throws UnknownItemException
     * @throws UnknownPageElementException
     * @throws YamlLoaderException
     *
     * @return TestInterface[]
     */
    public function load(string $path): array
    {
        $data = $this->yamlLoader->loadArray($path);
        if ([] === $data) {
            throw new EmptyTestException($path);
        }

        $singleBrowserDataSets = $this->createSingleBrowserDataSets($data);
        if ([] === $singleBrowserDataSets) {
            $singleBrowserDataSets = [$data];
        }

        $tests = [];

        foreach ($singleBrowserDataSets as $data) {
            $tests[] = $this->createTest($path, $data);
        }

        return $tests;
    }

    /**
     * @param array<mixed> $data
     *
     * @throws CircularStepImportException
     * @throws InvalidPageException
     * @throws InvalidTestException
     * @throws NonRetrievableImportException
     * @throws ParseException
     * @throws UnknownElementException
     * @throws UnknownItemException
     * @throws UnknownPageElementException
     */
    private function createTest(string $path, array $data): TestInterface
    {
        $basePath = dirname($path) . '/';

        try {
            $test = $this->testParser->parse($data);
        } catch (UnparseableTestException $unparseableTestException) {
            throw new ParseException($path, $path, $unparseableTestException);
        }

        $test = $test->withPath($path);

        $importsData = $data[self::DATA_KEY_IMPORTS] ?? [];
        $importsData = is_array($importsData) ? $importsData : [];

        $imports = $this->importsParser->parse($basePath, $importsData);

        try {
            $pageProvider = $this->createPageProvider($imports->getPagePaths());
            $stepProvider = $this->createStepProvider($path, $imports->getStepPaths());
            $dataSetProvider = $this->createDataSetProvider($imports->getDataProviderPaths());
        } catch (NonRetrievableImportException $nonRetrievableImportException) {
            $nonRetrievableImportException->setTestPath($path);

            throw $nonRetrievableImportException;
        } catch (InvalidPageException $invalidPageException) {
            $invalidPageException->setTestPath($path);

            throw $invalidPageException;
        }

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
     * @throws NonRetrievableImportException
     */
    private function createDataSetProvider(array $importPaths): DataSetProviderInterface
    {
        $dataSetCollections = [];

        foreach ($importPaths as $name => $path) {
            try {
                $dataSetCollections[$name] = $this->dataSetLoader->load($path);
            } catch (YamlLoaderException $yamlLoaderException) {
                throw new NonRetrievableImportException(
                    NonRetrievableImportException::TYPE_DATA_PROVIDER,
                    $name,
                    $path,
                    $yamlLoaderException
                );
            }
        }

        return new DataSetProvider($dataSetCollections);
    }

    /**
     * @param array<string, string> $importPaths
     *
     * @throws InvalidPageException
     * @throws NonRetrievableImportException
     */
    private function createPageProvider(array $importPaths): PageProviderInterface
    {
        $pages = [];

        foreach ($importPaths as $name => $path) {
            try {
                $pages[$name] = $this->pageLoader->load($name, $path);
            } catch (YamlLoaderException $yamlLoaderException) {
                throw new NonRetrievableImportException(
                    NonRetrievableImportException::TYPE_PAGE,
                    $name,
                    $path,
                    $yamlLoaderException
                );
            }
        }

        return new PageProvider($pages);
    }

    /**
     * @param array<string, string> $importPaths
     *
     * @throws NonRetrievableImportException
     * @throws ParseException
     */
    private function createStepProvider(string $testPath, array $importPaths): StepProviderInterface
    {
        $steps = [];

        foreach ($importPaths as $name => $path) {
            try {
                $steps[$name] = $this->stepLoader->load($path);
            } catch (YamlLoaderException $yamlLoaderException) {
                throw new NonRetrievableImportException(
                    NonRetrievableImportException::TYPE_STEP,
                    $name,
                    $path,
                    $yamlLoaderException
                );
            } catch (UnparseableStepException $unparseableStepException) {
                throw new ParseException($testPath, $path, $unparseableStepException);
            }
        }

        return new StepProvider($steps);
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<array<mixed>>
     */
    private function createSingleBrowserDataSets(array $data): array
    {
        $configData = $data['config'] ?? [];
        $configData = is_array($configData) ? $configData : [];

        $browsers = $configData['browsers'] ?? [];
        $browsers = is_array($browsers) ? $browsers : [];

        $url = $configData['url'] ?? '';

        $browserSpecificDataSets = [];
        foreach ($browsers as $browser) {
            $browserSpecificData = $data;
            $browserSpecificData['config'] = [
                'browser' => $browser,
                'url' => $url,
            ];

            $browserSpecificDataSets[] = $browserSpecificData;
        }

        return $browserSpecificDataSets;
    }
}
