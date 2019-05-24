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
use webignition\BasilParser\PageCollection\PageCollectionInterface;

class AssertionFactory
{
    private $identifierFactory;
    private $valueFactory;
    private $identifierStringExtractor;

    public function __construct()
    {
        $this->identifierFactory = new IdentifierFactory();
        $this->valueFactory = new ValueFactory();
        $this->identifierStringExtractor = new IdentifierStringExtractor();
    }

    /**
     * @param string $assertionString
     * @param PageCollectionInterface $pages
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
        PageCollectionInterface $pages
    ): AssertionInterface {
        $identifierString = $this->identifierStringExtractor->extractFromStart($assertionString);

        $identifier = $this->identifierFactory->create($identifierString, $pages);
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
