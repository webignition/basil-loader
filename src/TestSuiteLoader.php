<?php

declare(strict_types=1);

namespace webignition\BasilLoader;

use webignition\BasilLoader\Exception\NonRetrievableDataProviderException;
use webignition\BasilLoader\Exception\NonRetrievablePageException;
use webignition\BasilLoader\Exception\NonRetrievableStepException;
use webignition\BasilLoader\Exception\UnknownTestException;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModel\TestSuite\TestSuite;
use webignition\BasilModel\TestSuite\TestSuiteInterface;
use webignition\BasilModelFactory\Exception\EmptyAssertionStringException;
use webignition\BasilModelFactory\Exception\InvalidActionTypeException;
use webignition\BasilModelFactory\Exception\InvalidIdentifierStringException;
use webignition\BasilModelFactory\Exception\MissingComparisonException;
use webignition\BasilModelFactory\Exception\MissingValueException;
use webignition\BasilModelFactory\InvalidPageElementIdentifierException;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilModelProvider\Exception\UnknownDataProviderException;
use webignition\BasilModelProvider\Exception\UnknownPageException;
use webignition\BasilModelProvider\Exception\UnknownStepException;
use webignition\BasilModelResolver\CircularStepImportException;
use webignition\BasilModelResolver\UnknownElementException;
use webignition\BasilModelResolver\UnknownPageElementException;
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
     * @throws UnknownTestException
     * @throws YamlLoaderException
     */
    public function load(string $path): TestSuiteInterface
    {
        $basePath = dirname($path) . '/';
        $data = $this->yamlLoader->loadArray($path);
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
            }
        }

        return new TestSuite($path, $tests);
    }

    private function sanitizeData($data): array
    {
        if (!is_array($data)) {
            return [];
        }

        return array_filter($data, function ($item) {
            return is_string($item);
        });
    }

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
