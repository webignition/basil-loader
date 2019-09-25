<?php

namespace webignition\BasilLoader;

use webignition\BasilDataStructure\ImportList;
use webignition\BasilDataStructure\PathResolver;
use webignition\BasilLoader\Exception\NonRetrievableDataProviderException;
use webignition\BasilLoader\Exception\NonRetrievablePageException;
use webignition\BasilLoader\Exception\NonRetrievableStepException;
use webignition\BasilLoader\Exception\UnknownTestException;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModel\Exception\InvalidAssertionExaminedValueException;
use webignition\BasilModel\Exception\InvalidAssertionExpectedValueException;
use webignition\BasilModel\TestSuite\TestSuite;
use webignition\BasilModel\TestSuite\TestSuiteInterface;
use webignition\BasilModelFactory\Exception\EmptyAssertionStringException;
use webignition\BasilModelFactory\Exception\InvalidActionTypeException;
use webignition\BasilModelFactory\Exception\InvalidIdentifierStringException;
use webignition\BasilModelFactory\Exception\MissingValueException;
use webignition\BasilModelFactory\InvalidPageElementIdentifierException;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilModelProvider\Exception\UnknownDataProviderException;
use webignition\BasilModelProvider\Exception\UnknownPageException;
use webignition\BasilModelProvider\Exception\UnknownStepException;
use webignition\BasilModelResolver\CircularStepImportException;
use webignition\BasilModelResolver\UnknownElementException;
use webignition\BasilModelResolver\UnknownPageElementException;

class TestSuiteLoader
{
    private $yamlLoader;
    private $testLoader;
    private $pathResolver;

    public function __construct(YamlLoader $yamlLoader, TestLoader $testLoader, PathResolver $pathResolver)
    {
        $this->yamlLoader = $yamlLoader;
        $this->testLoader = $testLoader;
        $this->pathResolver = $pathResolver;
    }

    public static function createLoader(): TestSuiteLoader
    {
        return new TestSuiteLoader(
            YamlLoader::createLoader(),
            TestLoader::createLoader(),
            PathResolver::create()
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
     * @throws InvalidAssertionExaminedValueException
     * @throws InvalidAssertionExpectedValueException
     * @throws InvalidIdentifierStringException
     * @throws InvalidPageElementIdentifierException
     * @throws MalformedPageElementReferenceException
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
        $data = $this->yamlLoader->loadArray($path);
        $importList = new ImportList($this->pathResolver, dirname($path) . DIRECTORY_SEPARATOR, $data);

        $tests = [];

        foreach ($importList->getPaths() as $testImportIndex => $testImportPath) {
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
}
