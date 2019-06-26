<?php

namespace webignition\BasilParser\DataStructure;

class Page
{
    const KEY_URL = 'url';
    const KEY_ELEMENTS = 'elements';

    /**
     * @var array
     */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getUrlString(): string
    {
        $urlString = $this->data[self::KEY_URL] ?? '';

        return is_scalar($urlString) ? (string) $urlString : '';
    }

    public function getElementData(): array
    {
        $elementData = $this->data[self::KEY_ELEMENTS] ?? [];

        return is_array($elementData) ? $elementData : [];
    }
}
