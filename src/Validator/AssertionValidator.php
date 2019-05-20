<?php

namespace webignition\BasilParser\Validator;

use webignition\BasilParser\Model\Assertion\AssertionComparisons;
use webignition\BasilParser\Model\Assertion\AssertionInterface;

class AssertionValidator
{
    const REQUIRES_VALUE_COMPARISONS = [
        AssertionComparisons::IS,
        AssertionComparisons::IS_NOT,
        AssertionComparisons::INCLUDES,
        AssertionComparisons::EXCLUDES,
        AssertionComparisons::MATCHES,
    ];

    public function validate(AssertionInterface $assertion)
    {
        $requiresValue = in_array($assertion->getComparison(), self::REQUIRES_VALUE_COMPARISONS);

        if ($requiresValue && null ===$assertion->getValue()) {
            return false;
        }

        return true;
    }
}
