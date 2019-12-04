<?php

declare(strict_types=1);

namespace webignition\BasilLoader;

use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModels\Step\StepInterface;
use webignition\BasilParser\Exception\EmptyActionException;
use webignition\BasilParser\Exception\EmptyAssertionComparisonException;
use webignition\BasilParser\Exception\EmptyAssertionException;
use webignition\BasilParser\Exception\EmptyAssertionIdentifierException;
use webignition\BasilParser\Exception\EmptyAssertionValueException;
use webignition\BasilParser\Exception\EmptyInputActionValueException;
use webignition\BasilParser\StepParser;

class StepLoader
{
    private $yamlLoader;
    private $stepParser;

    public function __construct(YamlLoader $yamlLoader, StepParser $stepParser)
    {
        $this->yamlLoader = $yamlLoader;
        $this->stepParser = $stepParser;
    }

    public static function createLoader(): StepLoader
    {
        return new StepLoader(
            YamlLoader::createLoader(),
            StepParser::create()
        );
    }


    /**
     * @param string $path
     *
     * @return StepInterface
     *
     * @throws EmptyActionException
     * @throws EmptyAssertionComparisonException
     * @throws EmptyAssertionException
     * @throws EmptyAssertionIdentifierException
     * @throws EmptyAssertionValueException
     * @throws EmptyInputActionValueException
     * @throws YamlLoaderException
     */
    public function load(string $path): StepInterface
    {
        $data = $this->yamlLoader->loadArray($path);

        return $this->stepParser->parse($data);
    }
}
