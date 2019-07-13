<?php

namespace webignition\BasilParser\Provider\Test;

use webignition\BasilModel\Test\TestInterface;
use webignition\BasilParser\Exception\CircularStepImportException;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\NonRetrievableTestException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Exception\YamlLoaderException;
use webignition\BasilParser\Loader\TestLoader;

class DeferredTestProvider implements TestProviderInterface
{
    private $testLoader;
    private $tests = [];

    public function __construct(TestLoader $testLoader)
    {
        $this->testLoader = $testLoader;
    }

    /**
     * @param string $path
     *
     * @return TestInterface
     *
     * @throws CircularStepImportException
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableDataProviderException
     * @throws NonRetrievablePageException
     * @throws NonRetrievableStepException
     * @throws NonRetrievableTestException
     * @throws UnknownDataProviderException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws UnknownStepException
     */
    public function findTest(string $path): TestInterface
    {
        $test = $this->tests[$path] ?? null;

        if (null === $test) {
            $test = $this->retrieveTest($path);
            $this->tests[$path] = $test;
        }

        return $test;
    }

    /**
     * @param string[] $paths
     *
     * @return TestInterface[]
     *
     * @throws CircularStepImportException
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableDataProviderException
     * @throws NonRetrievablePageException
     * @throws NonRetrievableStepException
     * @throws NonRetrievableTestException
     * @throws UnknownDataProviderException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws UnknownStepException
     */
    public function findCollection(array $paths): array
    {
        $tests = [];

        foreach ($paths as $path) {
            if (is_string($path)) {
                $tests[] = $this->findTest($path);
            }
        }

        return $tests;
    }

    /**
     * @param string $path
     *
     * @return TestInterface
     *
     * @throws CircularStepImportException
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableDataProviderException
     * @throws NonRetrievablePageException
     * @throws NonRetrievableStepException
     * @throws NonRetrievableTestException
     * @throws UnknownDataProviderException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws UnknownStepException
     */
    private function retrieveTest(string $path): TestInterface
    {
        try {
            return $this->testLoader->load($path);
        } catch (YamlLoaderException $yamlLoaderException) {
            throw new NonRetrievableTestException($path, $yamlLoaderException);
        }
    }
}
