<?php

declare(strict_types=1);

namespace webignition\BasilLoader;

use webignition\BasilLoader\Exception\EmptyTestException;
use webignition\BasilLoader\Exception\InvalidPageException;
use webignition\BasilLoader\Exception\InvalidTestException;
use webignition\BasilLoader\Exception\NonRetrievableImportException;
use webignition\BasilLoader\Exception\ParseException;
use webignition\BasilLoader\Exception\UnknownTestException;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModelProvider\Exception\UnknownItemException;
use webignition\BasilModels\TestSuite\TestSuiteInterface;
use webignition\BasilParser\Test\TestParser;
use webignition\BasilResolver\CircularStepImportException;
use webignition\BasilResolver\UnknownElementException;
use webignition\BasilResolver\UnknownPageElementException;

class SourceLoader
{
    private YamlLoader $yamlLoader;
    private TestParser $testParser;
    private TestLoader $testLoader;
    private TestSuiteLoader $testSuiteLoader;

    public function __construct(
        YamlLoader $yamlLoader,
        TestParser $testParser,
        TestLoader $testLoader,
        TestSuiteLoader $testSuiteLoader
    ) {
        $this->yamlLoader = $yamlLoader;
        $this->testParser = $testParser;
        $this->testLoader = $testLoader;
        $this->testSuiteLoader = $testSuiteLoader;
    }

    public static function createLoader(): SourceLoader
    {
        return new SourceLoader(
            YamlLoader::createLoader(),
            TestParser::create(),
            TestLoader::createLoader(),
            TestSuiteLoader::createLoader()
        );
    }

    /**
     * @param string $path
     *
     * @return TestSuiteInterface
     *
     * @throws CircularStepImportException
     * @throws EmptyTestException
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

        if ([] === $data) {
            throw new EmptyTestException($path);
        }

        if (!$this->isTestPathList($data)) {
            $data = [
                0 => $path,
            ];
        }

        return $this->testSuiteLoader->loadFromTestPathList($path, $basePath, $data);
    }

    /**
     * @param array<mixed> $data
     *
     * @return bool
     */
    private function isTestPathList(array $data): bool
    {
        if ([] === $data) {
            return false;
        }

        $keysAreAllIntegers = array_reduce(array_keys($data), function ($result, $value) {
            return false === $result ? false : is_int($value);
        });

        if (false === $keysAreAllIntegers) {
            return false;
        }

        return array_reduce(array_values($data), function ($result, $value) {
            return false === $result ? false : is_string($value);
        });
    }
}
