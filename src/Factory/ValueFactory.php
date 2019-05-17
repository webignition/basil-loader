<?php

namespace webignition\BasilParser\Factory;

use webignition\BasilParser\Model\Value\Value;
use webignition\BasilParser\Model\Value\ValueInterface;
use webignition\BasilParser\Model\Value\ValueTypes;

class ValueFactory
{
    public function createFromValueString(string $valueString): ValueInterface
    {
        $valueString = trim($valueString);
        $type = ValueTypes::STRING;

        if ('$' === $valueString[0]) {
            $type = ValueTypes::DATA_PARAMETER;
        } else {
            if ('"' === $valueString[0]) {
                $valueString = mb_substr($valueString, 1);
            }

            if ('"' === $valueString[-1]) {
                $valueString = mb_substr($valueString, 0, -1);
            }

            $valueString = str_replace('\\"', '"', $valueString);
        }

        return new Value($type, $valueString);
    }
}
