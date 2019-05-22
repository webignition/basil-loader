<?php

namespace webignition\BasilParser\Model\DataSet;

interface DataSetInterface
{
    public function getParameterValue(string $parameterName): ?string;

    /**
     * @return string[]
     */
    public function getParameterNames(): array;
}
