<?php

namespace webignition\BasilParser\DataStructure;

class Page extends AbstractDataStructure
{
    const KEY_URL = 'url';
    const KEY_ELEMENTS = 'elements';

    public function getUrlString(): string
    {
        return $this->getString(self::KEY_URL);
    }

    public function getElementData(): array
    {
        return $this->getArray(self::KEY_ELEMENTS);
    }
}
