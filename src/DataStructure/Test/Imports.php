<?php

namespace webignition\BasilParser\DataStructure\Test;

use webignition\BasilParser\DataStructure\AbstractDataStructure;
use webignition\BasilParser\DataStructure\ImportList;

class Imports extends AbstractDataStructure
{
    const KEY_STEPS = 'steps';
    const KEY_PAGES = 'pages';
    const KEY_DATA_PROVIDERS = 'data_providers';

    private $stepPaths;
    private $pagePaths;
    private $dataProviderPaths;

    public function __construct(array $data, string $basePath)
    {
        parent::__construct($data);

        $this->stepPaths = new ImportList($this->getArray(self::KEY_STEPS), $basePath);
        $this->pagePaths = new ImportList($this->getArray(self::KEY_PAGES), $basePath);
        $this->dataProviderPaths = new ImportList($this->getArray(self::KEY_DATA_PROVIDERS), $basePath);
    }

    public function getStepPaths(): array
    {
        return $this->stepPaths->getPaths();
    }

    public function getPagePaths(): array
    {
        return $this->pagePaths->getPaths();
    }

    public function getDataProviderPaths(): array
    {
        return $this->dataProviderPaths->getPaths();
    }
}
