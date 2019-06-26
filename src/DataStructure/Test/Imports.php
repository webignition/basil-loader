<?php

namespace webignition\BasilParser\DataStructure\Test;

use webignition\BasilParser\DataStructure\AbstractDataStructure;

class Imports extends AbstractDataStructure
{
    const KEY_STEPS = 'steps';
    const KEY_PAGES = 'pages';
    const KEY_DATA_PROVIDERS = 'data_providers';

    public function getStepPaths(): array
    {
        return $this->getArray(self::KEY_STEPS);
    }

    public function getPagePaths(): array
    {
        return $this->getArray(self::KEY_PAGES);
    }

    public function getDataProviderPaths(): array
    {
        return $this->getArray(self::KEY_DATA_PROVIDERS);
    }
}
