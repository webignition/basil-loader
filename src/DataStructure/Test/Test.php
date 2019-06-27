<?php

namespace webignition\BasilParser\DataStructure\Test;

use webignition\BasilParser\DataStructure\AbstractDataStructure;
use webignition\BasilParser\DataStructure\Step;

class Test extends AbstractDataStructure
{
    const KEY_CONFIGURATION = 'config';
    const KEY_IMPORTS = 'imports';

    private $path = '';

    public function __construct(array $data, string $path = '')
    {
        parent::__construct($data);

        $this->path = $path;
    }

    public function getConfiguration(): Configuration
    {
        return new Configuration($this->data[self::KEY_CONFIGURATION] ?? []);
    }

    public function getImports(): Imports
    {
        $basePath = dirname($this->path) . DIRECTORY_SEPARATOR;

        return new Imports($this->data[self::KEY_IMPORTS] ?? [], $basePath);
    }

    public function getSteps(): array
    {
        $stepNames = array_diff(array_keys($this->data), [self::KEY_CONFIGURATION, self::KEY_IMPORTS]);

        $steps = [];

        foreach ($stepNames as $stepName) {
            $steps[$stepName] = new Step($this->data[$stepName]);
        }

        return $steps;
    }
}
