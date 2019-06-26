<?php

namespace webignition\BasilParser\DataStructure\Test;

use webignition\BasilParser\DataStructure\AbstractDataStructure;

class Configuration extends AbstractDataStructure
{
    const KEY_BROWSER = 'browser';
    const KEY_URL = 'url';

    public function getBrowser(): string
    {
        return $this->getString(self::KEY_BROWSER);
    }

    public function getUrl(): string
    {
        return $this->getString(self::KEY_URL);
    }
}
