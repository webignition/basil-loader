<?php

namespace webignition\BasilParser\Factory;

use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\IdentifierStringExtractor\IdentifierStringExtractor;
use webignition\BasilParser\Model\Assertion\Assertion;
use webignition\BasilParser\Model\Assertion\AssertionComparisons;
use webignition\BasilParser\Model\Assertion\AssertionInterface;
use webignition\BasilParser\Provider\Page\PageProviderInterface;

class AssertionFactory
{
    private $identifierFactory;
    private $valueFactory;
    private $identifierStringExtractor;

    public function __construct(
        IdentifierFactory $identifierFactory,
        ValueFactory $valueFactory,
        IdentifierStringExtractor $identifierStringExtractor
    ) {
        $this->identifierFactory = $identifierFactory;
        $this->valueFactory = $valueFactory;
        $this->identifierStringExtractor = $identifierStringExtractor;
    }

    /**
     * @param string $assertionString
     * @param PageProviderInterface $pageProvider
     *
     * @return AssertionInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws NonRetrievablePageException
     */
    public function createFromAssertionString(
        string $assertionString,
        PageProviderInterface $pageProvider
    ): AssertionInterface {
        $identifierString = $this->identifierStringExtractor->extractFromStart($assertionString);

        $identifier = $this->identifierFactory->create($identifierString, $pageProvider);
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
                $value = $this->valueFactory->createFromValueString($valueString);
            }
        }

        return new Assertion($assertionString, $identifier, $comparison, $value);
    }
}
