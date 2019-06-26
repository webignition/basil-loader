<?php

namespace webignition\BasilParser\DataStructure;

class Step
{
    const KEY_ACTIONS = 'actions';
    const KEY_ASSERTIONS = 'assertions';

    /**
     * @var array
     */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getActionStrings(): array
    {
        return $this->getStringArray(self::KEY_ACTIONS);
    }

    public function getAssertionStrings(): array
    {
        return $this->getStringArray(self::KEY_ASSERTIONS);
    }

    private function getStringArray(string $key): array
    {
        $strings = $this->data[$key] ?? [];

        return is_array($strings) ? $strings : [];
    }
}
