<?php

namespace webignition\BasilParser\Loader;

use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Exception\YamlLoaderException;
use webignition\BasilParser\Factory\Test\TestFactory;
use webignition\BasilParser\Model\Test\TestInterface;

class TestLoader
{
    private $yamlLoader;
    private $testFactory;

    public function __construct(YamlLoader $yamlLoader, TestFactory $testFactory)
    {
        $this->yamlLoader = $yamlLoader;
        $this->testFactory = $testFactory;
    }

    /**
     * @param string $path
     *
     * @return TestInterface
     *
     * @throws YamlLoaderException
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableDataProviderException
     * @throws NonRetrievablePageException
     * @throws NonRetrievableStepException
     * @throws UnknownDataProviderException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws UnknownStepException
     */
    public function load(string $path): TestInterface
    {
        $data = $this->yamlLoader->loadArray($path);

        return $this->testFactory->createFromTestData($path, $data);
    }
}
