<?php

namespace webignition\BasilParser\DataStructure;

class Step
{
    const KEY_ACTIONS = 'actions';
    const KEY_ASSERTIONS = 'assertions';
    const KEY_USE = 'use';
    const KEY_DATA = 'data';
    const KEY_ELEMENTS = 'elements';

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

    public function getImportName(): string
    {
        $importName = $this->data[self::KEY_USE] ?? '';

        return is_string($importName) ? $importName : '';
    }

    public function getDataArray(): array
    {
        $data = $this->data[self::KEY_DATA] ?? [];

        return is_array($data) ? $data : [];
    }

    public function getDataImportName(): string
    {
        $dataImportName = $this->data[self::KEY_DATA] ?? '';

        return is_string($dataImportName) ? $dataImportName : '';
    }

    public function getElementStrings(): array
    {
        $elements = $this->data[self::KEY_ELEMENTS] ?? [];

        return is_array($elements) ? $elements : [];
    }

    private function getStringArray(string $key): array
    {
        $strings = $this->data[$key] ?? [];

        return is_array($strings) ? $strings : [];
    }
}
