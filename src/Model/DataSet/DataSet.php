<?php

namespace webignition\BasilParser\Model\DataSet;

class DataSet implements DataSetInterface
{
    private $data = [];

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $this->data[(string) $key] = (string) $value;
        }
    }

    public function getParameterValue(string $parameterName): ?string
    {
        return $this->data[$parameterName] ?? null;
    }

    /**
     * @return string[]
     */
    public function getParameterNames(): array
    {
        return array_keys($this->data);
    }
}
