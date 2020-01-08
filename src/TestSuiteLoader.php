<?php

declare(strict_types=1);

namespace webignition\BasilLoader;

use webignition\BasilLoader\Exception\InvalidPageException;
use webignition\BasilLoader\Exception\InvalidTestException;
use webignition\BasilLoader\Exception\NonRetrievableDataProviderException;
use webignition\BasilLoader\Exception\NonRetrievablePageException;
use webignition\BasilLoader\Exception\NonRetrievableStepException;
use webignition\BasilLoader\Exception\UnknownTestException;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModelProvider\Exception\UnknownDataProviderException;
use webignition\BasilModelProvider\Exception\UnknownPageException;
use webignition\BasilModelProvider\Exception\UnknownStepException;
use webignition\BasilModels\TestSuite\TestSuite;
use webignition\BasilModels\TestSuite\TestSuiteInterface;
use webignition\BasilParser\Exception\EmptyActionException;
use webignition\BasilParser\Exception\EmptyAssertionComparisonException;
use webignition\BasilParser\Exception\EmptyAssertionException;
use webignition\BasilParser\Exception\EmptyAssertionIdentifierException;
use webignition\BasilParser\Exception\EmptyAssertionValueException;
use webignition\BasilParser\Exception\EmptyInputActionValueException;
use webignition\BasilParser\Exception\InvalidActionIdentifierException;
use webignition\BasilResolver\CircularStepImportException;
use webignition\BasilResolver\UnknownElementException;
use webignition\BasilResolver\UnknownPageElementException;
use webignition\PathResolver\PathResolver;

class TestSuiteLoader
{
    private $yamlLoader;
    private $testLoader;
    private $pathResolver;

    public function __construct(
        YamlLoader $yamlLoader,
        TestLoader $testLoader,
        PathResolver $pathResolver
    ) {
        $this->yamlLoader = $yamlLoader;
        $this->testLoader = $testLoader;
        $this->pathResolver = $pathResolver;
    }

    public static function createLoader(): TestSuiteLoader
    {
        return new TestSuiteLoader(
            YamlLoader::createLoader(),
            TestLoader::createLoader(),
            new PathResolver()
        );
    }

    /**
     * @param string $path
     *
     * @return TestSuiteInterface
     *
     * @throws CircularStepImportException
     * @throws EmptyActionException
     * @throws EmptyAssertionComparisonException
     * @throws EmptyAssertionException
     * @throws EmptyAssertionIdentifierException
     * @throws EmptyAssertionValueException
     * @throws EmptyInputActionValueException
     * @throws InvalidActionIdentifierException
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
     * @throws UnknownTestException
     * @throws YamlLoaderException
     */
    public function load(string $path): TestSuiteInterface
    {
        $basePath = dirname($path) . '/';
        $data = $this->yamlLoader->loadArray($path);

        return $this->loadFromTestPathList($path, $basePath, $data);
    }

    /**
     * @param string $path
     * @param string $basePath
     * @param string[] $data
     *
     * @return TestSuiteInterface
     *
     * @throws CircularStepImportException
     * @throws EmptyActionException
     * @throws EmptyAssertionComparisonException
     * @throws EmptyAssertionException
     * @throws EmptyAssertionIdentifierException
     * @throws EmptyAssertionValueException
     * @throws EmptyInputActionValueException
     * @throws InvalidActionIdentifierException
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
     * @throws UnknownTestException
     * @throws YamlLoaderException
     */
    public function loadFromTestPathList(string $path, string $basePath, array $data): TestSuiteInterface
    {
        $paths = $this->sanitizeData($data);
        $resolvedPaths = $this->resolvePaths($basePath, $paths);

        $tests = [];

        foreach ($resolvedPaths as $testImportPath) {
            $testImportPath = (string) $testImportPath;

            try {
                $tests[] = $this->testLoader->load($testImportPath);
            } catch (YamlLoaderException $yamlLoaderException) {
                $isFileCannotBeOpenedException =
                    $yamlLoaderException->isFileDoesNotExistException() ||
                    $yamlLoaderException->isFileCannotBeReadException();

                if ($isFileCannotBeOpenedException && $testImportPath === $yamlLoaderException->getPath()) {
                    throw new UnknownTestException($testImportPath);
                }

                throw $yamlLoaderException;
            }
        }

        return new TestSuite($path, $tests);
    }

    /**
     * @param mixed $data
     *
     * @return array<mixed>
     */
    private function sanitizeData($data): array
    {
        if (!is_array($data)) {
            return [];
        }

        return array_filter($data, function ($item) {
            return is_string($item);
        });
    }

    /**
     * @param string $basePath
     * @param array<mixed> $paths
     *
     * @return string[]
     */
    private function resolvePaths(string $basePath, array $paths): array
    {
        return array_map(
            function ($path) use ($basePath) {
                return $this->pathResolver->resolve($basePath, $path);
            },
            $paths
        );
    }
}
