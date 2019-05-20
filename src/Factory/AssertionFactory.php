<?php

namespace webignition\BasilParser\Factory;

use webignition\BasilParser\IdentifierStringExtractor\IdentifierStringExtractor;
use webignition\BasilParser\Model\Assertion\Assertion;
use webignition\BasilParser\Model\Assertion\AssertionComparisons;
use webignition\BasilParser\Model\Assertion\AssertionInterface;

class AssertionFactory
{
    private $identifierFactory;
    private $assertionValueFactory;
    private $identifierStringExtractor;

    const IDENTIFIER_STRING_STOP_STRINGS = [
        ' is ',
        ' is-not ',
        ' exists',
        ' not-exists',
        ' includes ',
        ' excludes ',
        ' matches ',
    ];

    public function __construct(
        IdentifierFactory $identifierFactory = null,
        ValueFactory $assertionValueFactory = null,
        IdentifierStringExtractor $identifierStringExtractor = null
    ) {
        $this->identifierFactory = $identifierFactory;
        $this->assertionValueFactory = $assertionValueFactory;
        $this->identifierStringExtractor = $identifierStringExtractor;
    }

    public static function create()
    {
        return new AssertionFactory(
            new IdentifierFactory(),
            new ValueFactory(),
            IdentifierStringExtractor::create()
        );
    }

    public function createFromAssertionString(string $assertionString): AssertionInterface
    {
        $identifierString = $this->identifierStringExtractor->extractFromStart($assertionString);

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

        return new Assertion($assertionString, $identifier, $comparison, $value);
    }
}
