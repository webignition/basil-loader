<?php

namespace webignition\BasilParser\Factory\Assertion;

use webignition\BasilParser\Factory\ValueFactory;
use webignition\BasilParser\Factory\IdentifierFactory;
use webignition\BasilParser\Model\Assertion\Assertion;
use webignition\BasilParser\Model\Assertion\AssertionComparisons;
use webignition\BasilParser\Model\Assertion\AssertionInterface;

class AssertionFactory
{
    const IDENTIFIER_REGEX =
        '/.+?(?=(( is )|( is-not )|( exists)|( not-exists)|( includes )|( excludes )|( matches )))/';

    private $identifierFactory;
    private $assertionValueFactory;

    public function __construct(
        ?IdentifierFactory $identifierFactory = null,
        ?ValueFactory $assertionValueFactory = null
    ) {
        $identifierFactory = $identifierFactory ?? new IdentifierFactory();
        $assertionValueFactory = $assertionValueFactory ?? new ValueFactory();

        $this->identifierFactory = $identifierFactory;
        $this->assertionValueFactory = $assertionValueFactory;
    }

    public function createFromAssertionString(string $assertionString): AssertionInterface
    {
        $identifierMatches = [];
        preg_match_all(self::IDENTIFIER_REGEX, $assertionString, $identifierMatches);

        $identifierString = implode($identifierMatches[0], '');

        $identifier = $this->identifierFactory->create($identifierString);
        $value = null;

        $comparisonAndValue = trim(mb_substr($assertionString, mb_strlen($identifierString)));

        if (substr_count($comparisonAndValue, ' ') === 0) {
            $comparison = $comparisonAndValue;
            $valueString = null;
        } else {
            $comparisonAndValueParts = explode(' ', $comparisonAndValue, 2);
            list($comparison, $valueString) = $comparisonAndValueParts;

            if (in_array($comparison, AssertionComparisons::NO_VALUE_TYPES)) {
                $value = null;
            } else {
                $value = $this->assertionValueFactory->createFromValueString($valueString);
            }
        }

        return new Assertion($identifier, $comparison, $value);
    }
}
