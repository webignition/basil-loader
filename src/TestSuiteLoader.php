<?php

declare(strict_types=1);

namespace webignition\BasilLoader;

use webignition\BasilLoader\Exception\InvalidPageException;
use webignition\BasilLoader\Exception\InvalidTestException;
use webignition\BasilLoader\Exception\NonRetrievableImportException;
use webignition\BasilLoader\Exception\ParseException;
use webignition\BasilLoader\Exception\UnknownTestException;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModelProvider\Exception\UnknownItemException;
use webignition\BasilModels\TestSuite\TestSuite;
use webignition\BasilModels\TestSuite\TestSuiteInterface;
use webignition\BasilResolver\CircularStepImportException;
use webignition\BasilResolver\UnknownElementException;
use webignition\BasilResolver\UnknownPageElementException;
use webignition\PathResolver\PathResolver;

class TestSuiteLoader
{
    public function __construct(
        private YamlLoader $yamlLoader,
        private TestLoader $testLoader,
        private PathResolver $pathResolver
    ) {
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
     * @throws CircularStepImportException
     * @throws InvalidPageException
     * @throws InvalidTestException
     * @throws NonRetrievableImportException
     * @throws ParseException
     * @throws UnknownElementException
     * @throws UnknownItemException
     * @throws UnknownPageElementException
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
     * @param string[] $data
     *
     * @throws CircularStepImportException
     * @throws InvalidPageException
     * @throws InvalidTestException
     * @throws NonRetrievableImportException
     * @throws ParseException
     * @throws UnknownElementException
     * @throws UnknownItemException
     * @throws UnknownPageElementException
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
                $tests = array_merge($tests, $this->testLoader->load($testImportPath));
            } catch (YamlLoaderException $yamlLoaderException) {
                $isFileCannotBeOpenedException =
                    $yamlLoaderException->isFileDoesNotExistException()
                    || $yamlLoaderException->isFileCannotBeReadException();

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
