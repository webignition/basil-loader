<?php

namespace webignition\BasilLoader;

use webignition\BasilDataStructure\Step as StepData;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModelFactory\Exception\EmptyAssertionStringException;
use webignition\BasilModelFactory\Exception\InvalidActionTypeException;
use webignition\BasilModelFactory\Exception\InvalidIdentifierStringException;
use webignition\BasilModelFactory\Exception\MissingValueException;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilModelFactory\StepFactory;

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
     * @throws EmptyAssertionStringException
     * @throws InvalidActionTypeException
     * @throws InvalidIdentifierStringException
     * @throws MalformedPageElementReferenceException
     * @throws MissingValueException
     * @throws YamlLoaderException
     */
    public function load(string $path): StepInterface
    {
        $data = $this->yamlLoader->loadArray($path);
        $stepData = new StepData($data);

        return $this->stepFactory->createFromStepData($stepData);
    }
}
