<?php

namespace webignition\BasilParser\Factory\Assertion;

use webignition\BasilParser\Model\Assertion\AssertionValue;
use webignition\BasilParser\Model\Assertion\AssertionValueInterface;
use webignition\BasilParser\Model\Assertion\AssertionValueTypes;

class AssertionValueFactory
{
    public function createFromValueString(string $valueString): AssertionValueInterface
    {
        $valueString = trim($valueString);
        $type = AssertionValueTypes::STRING;

        if ('$' === $valueString[0]) {
            $type = AssertionValueTypes::DATA_PARAMETER;
        } else {
            if ('"' === $valueString[0]) {
                $valueString = mb_substr($valueString, 1);
            }

            if ('"' === $valueString[-1]) {
                $valueString = mb_substr($valueString, 0, -1);
            }

            $valueString = str_replace('\\"', '"', $valueString);
        }

        return new AssertionValue($type, $valueString);
    }
}
