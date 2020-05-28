<?php

declare(strict_types=1);

namespace webignition\BasilLoader;

use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModels\Step\StepInterface;
use webignition\BasilParser\Exception\UnparseableStepException;
use webignition\BasilParser\StepParser;

class StepLoader
{
    private YamlLoader $yamlLoader;
    private StepParser $stepParser;

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
     * @throws UnparseableStepException
     * @throws YamlLoaderException
     */
    public function load(string $path): StepInterface
    {
        $data = $this->yamlLoader->loadArray($path);

        return $this->stepParser->parse($data);
    }
}
