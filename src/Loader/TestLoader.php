<?php

namespace webignition\BasilParser\Loader;

use webignition\BasilModel\Test\TestInterface;
use webignition\BasilParser\DataStructure\Test\Test as TestData;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Exception\YamlLoaderException;
use webignition\BasilParser\Factory\Test\TestFactory;
use webignition\BasilParser\PathResolver\PathResolver;

class TestLoader
{
    private $yamlLoader;
    private $testFactory;
    private $pathResolver;

    public function __construct(YamlLoader $yamlLoader, TestFactory $testFactory, PathResolver $pathResolver)
    {
        $this->yamlLoader = $yamlLoader;
        $this->testFactory = $testFactory;
        $this->pathResolver = $pathResolver;
    }

    /**
     * @param string $path
     *
     * @return TestInterface
     *
     * @throws YamlLoaderException
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableDataProviderException
     * @throws NonRetrievableStepException
     * @throws UnknownDataProviderException
     * @throws UnknownStepException
     */
    public function load(string $path): TestInterface
    {
        $data = $this->yamlLoader->loadArray($path);
        $testData = new TestData($this->pathResolver, $data, $path);

        return $this->testFactory->createFromTestData($path, $testData);
    }
}
