<?php

namespace webignition\BasilParser\Validator;

use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\Assertion\AssertionInterface;

class AssertionValidator
{
    const REQUIRES_VALUE_COMPARISONS = [
        AssertionComparisons::IS,
        AssertionComparisons::IS_NOT,
        AssertionComparisons::INCLUDES,
        AssertionComparisons::EXCLUDES,
        AssertionComparisons::MATCHES,
    ];

    public function validate(AssertionInterface $assertion): bool
    {
        if (null === $assertion->getIdentifier()) {
            return false;
        }

        if ('' === $assertion->getComparison()) {
            return false;
        }

        $requiresValue = in_array($assertion->getComparison(), self::REQUIRES_VALUE_COMPARISONS);

        if ($requiresValue && null ===$assertion->getValue()) {
            return false;
        }

        return true;
    }
}
