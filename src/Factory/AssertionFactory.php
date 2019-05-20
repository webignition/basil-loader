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
        ?IdentifierFactory $identifierFactory = null,
        ?ValueFactory $assertionValueFactory = null,
        ?IdentifierStringExtractor $identifierStringExtractor = null
    ) {
        $identifierFactory = $identifierFactory ?? new IdentifierFactory();
        $assertionValueFactory = $assertionValueFactory ?? new ValueFactory();
        $identifierStringExtractor = $identifierStringExtractor ?? new IdentifierStringExtractor();

        $this->identifierFactory = $identifierFactory;
        $this->assertionValueFactory = $assertionValueFactory;
        $this->identifierStringExtractor = $identifierStringExtractor;
    }

    public function createFromAssertionString(string $assertionString): AssertionInterface
    {
        $identifierString = $this->identifierStringExtractor->extractFromStart(
            $assertionString,
            self::IDENTIFIER_STRING_STOP_STRINGS
        );

        if ('' === $identifierString) {
            var_dump($assertionString, $identifierString);
            exit();
        }

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
