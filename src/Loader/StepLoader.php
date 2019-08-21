<?php

namespace webignition\BasilLoader\Loader;

use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilModelFactory\StepFactory;
use webignition\BasilDataStructure\Step as StepData;
use webignition\BasilLoader\Exception\YamlLoaderException;

class StepLoader
{
    private $yamlLoader;
    private $stepFactory;

    public function __construct(YamlLoader $yamlLoader, StepFactory $stepFactory)
    {
        $this->yamlLoader = $yamlLoader;
        $this->stepFactory = $stepFactory;
    }

    public static function createLoader(): StepLoader
    {
        return new StepLoader(
            YamlLoader::createLoader(),
            StepFactory::createFactory()
        );
    }


    /**
     * @param string $path
     *
     * @return StepInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws YamlLoaderException
     */
    public function load(string $path): StepInterface
    {
        $data = $this->yamlLoader->loadArray($path);
        $stepData = new StepData($data);

        return $this->stepFactory->createFromStepData($stepData);
    }
}
