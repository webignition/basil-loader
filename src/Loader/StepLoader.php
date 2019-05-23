<?php

namespace webignition\BasilParser\Loader;

use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Model\Step\StepInterface;

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
     */
    public function load(string $path): StepInterface
    {
        $data = $this->yamlLoader->loadArray($path);

        return $this->stepFactory->createFromStepData($data);
    }
}
