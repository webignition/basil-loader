<?php

namespace webignition\BasilParser\Loader;

use webignition\BasilModel\Step\StepInterface;
use webignition\BasilParser\DataStructure\Step as StepData;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\YamlLoaderException;
use webignition\BasilParser\Factory\StepFactory;

class StepLoader
{
    private $yamlLoader;
    private $stepFactory;

    public function __construct(YamlLoader $yamlLoader, StepFactory $stepFactory)
    {
        $this->yamlLoader = $yamlLoader;
        $this->stepFactory = $stepFactory;
    }

    /**
     * @param string $path
     *
     * @return StepInterface
     *
     * @throws YamlLoaderException
     * @throws MalformedPageElementReferenceException
     */
    public function load(string $path): StepInterface
    {
        $data = $this->yamlLoader->loadArray($path);
        $stepData = new StepData($data);

        return $this->stepFactory->createFromStepData($stepData);
    }
}
