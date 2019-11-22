<?php

declare(strict_types=1);

namespace webignition\BasilLoader;

use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModelFactory\Exception\EmptyAssertionStringException;
use webignition\BasilModelFactory\Exception\InvalidActionTypeException;
use webignition\BasilModelFactory\Exception\InvalidIdentifierStringException;
use webignition\BasilModelFactory\Exception\MissingComparisonException;
use webignition\BasilModelFactory\Exception\MissingValueException;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilModelFactory\StepFactory;
use webignition\BasilParser\StepParser;

class StepLoader
{
    private $yamlLoader;
    private $stepParser;
    private $stepFactory;

    public function __construct(YamlLoader $yamlLoader, StepParser $stepParser, StepFactory $stepFactory)
    {
        $this->yamlLoader = $yamlLoader;
        $this->stepParser = $stepParser;
        $this->stepFactory = $stepFactory;
    }

    public static function createLoader(): StepLoader
    {
        return new StepLoader(
            YamlLoader::createLoader(),
            StepParser::create(),
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
     * @throws MissingComparisonException
     * @throws MissingValueException
     * @throws YamlLoaderException
     */
    public function load(string $path): StepInterface
    {
        $data = $this->yamlLoader->loadArray($path);
        $stepData = $this->stepParser->parse($data);

        return $this->stepFactory->createFromStepData($stepData);
    }
}
