<?php

namespace webignition\BasilParser\Loader;

use webignition\BasilModel\TestSuite\TestSuite;
use webignition\BasilModel\TestSuite\TestSuiteInterface;
use webignition\BasilParser\DataStructure\ImportList;
use webignition\BasilParser\Exception\CircularStepImportException;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Exception\UnknownTestException;
use webignition\BasilParser\Exception\YamlLoaderException;
use webignition\BasilParser\PathResolver\PathResolver;

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

    /**
     * @param string $path
     *
     * @return TestSuiteInterface
     *
     * @throws CircularStepImportException
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableDataProviderException
     * @throws NonRetrievablePageException
     * @throws NonRetrievableStepException
     * @throws UnknownDataProviderException
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
