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
use webignition\BasilModels\TestSuite\TestSuiteInterface;
use webignition\BasilParser\Exception\EmptyActionException;
use webignition\BasilParser\Exception\EmptyAssertionComparisonException;
use webignition\BasilParser\Exception\EmptyAssertionException;
use webignition\BasilParser\Exception\EmptyAssertionIdentifierException;
use webignition\BasilParser\Exception\EmptyAssertionValueException;
use webignition\BasilParser\Exception\EmptyInputActionValueException;
use webignition\BasilParser\Test\TestParser;
use webignition\BasilResolver\CircularStepImportException;
use webignition\BasilResolver\UnknownElementException;
use webignition\BasilResolver\UnknownPageElementException;

class SourceLoader
{
    private $yamlLoader;
    private $testParser;
    private $testLoader;
    private $testSuiteLoader;

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
     * @throws UnknownTestException
     * @throws YamlLoaderException
     */
    public function load(string $path): TestSuiteInterface
    {
        $basePath = dirname($path) . '/';
        $data = $this->yamlLoader->loadArray($path);

        if (!$this->isTestPathList($data)) {
            $data = [
                0 => $path,
            ];
        }

        return $this->testSuiteLoader->loadFromTestPathList($path, $basePath, $data);
    }

    private function isTestPathList(array $data): bool
    {
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
