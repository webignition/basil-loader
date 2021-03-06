<?php

declare(strict_types=1);

namespace webignition\BasilLoader;

use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModels\Step\StepInterface;
use webignition\BasilParser\Exception\UnparseableStepException;
use webignition\BasilParser\StepParser;

class StepLoader
{
    public function __construct(
        private YamlLoader $yamlLoader,
        private StepParser $stepParser
    ) {
    }

    public static function createLoader(): StepLoader
    {
        return new StepLoader(
            YamlLoader::createLoader(),
            StepParser::create()
        );
    }

    /**
     * @throws UnparseableStepException
     * @throws YamlLoaderException
     */
    public function load(string $path): StepInterface
    {
        $data = $this->yamlLoader->loadArray($path);

        return $this->stepParser->parse($data);
    }
}
