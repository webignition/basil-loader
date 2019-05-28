<?php

namespace webignition\BasilParser\Factory;

use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierInterface;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;
use webignition\BasilParser\Model\PageElementReference\PageElementReference;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;
use webignition\BasilParser\Provider\Page\PageProviderInterface;

class IdentifierFactory
{
    const POSITION_FIRST = 'first';
    const POSITION_LAST = 'last';

    const POSITION_PATTERN = ':(-?[0-9]+|first|last)';
    const POSITION_REGEX = '/' . self::POSITION_PATTERN . '$/';
    const CSS_SELECTOR_REGEX = '/^"((?!\/).).+("|' . self::POSITION_PATTERN . ')$/';
    const XPATH_EXPRESSION_REGEX = '/^"\/.+("|' . self::POSITION_PATTERN . ')$/';
    const ELEMENT_PARAMETER_REGEX = '/^\$elements.+/';
    const PAGE_OBJECT_PARAMETER_REGEX = '/^\$page.+/';
    const BROWSER_OBJECT_PARAMETER_REGEX = '/^\$browser.+/';
    const REFERENCED_ELEMENT_REGEX = '/^"{{.+/';
    const REFERENCED_ELEMENT_EXTRACTOR_REGEX = '/^".+?(?=(}}))}}/';

    /**
     * @param string $identifierString
     * @param string|null $elementName
     * @param IdentifierInterface[] $existingIdentifiers
     *
     * @return IdentifierInterface|null
     *
     * @throws MalformedPageElementReferenceException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws NonRetrievablePageException
     */
    public function createWithElementReference(
        string $identifierString,
        ?string $elementName,
        array $existingIdentifiers
    ): ?IdentifierInterface {
        $identifierString = trim($identifierString);

        if (empty($identifierString)) {
            return null;
        }

        $parentIdentifierName = null;

        if (1 === preg_match(self::REFERENCED_ELEMENT_REGEX, $identifierString)) {
            list($parentIdentifierName, $identifierString) =
                $this->extractElementReferenceAndIdentifierString($identifierString);
        }

        $parentIdentifier = $existingIdentifiers[$parentIdentifierName] ?? null;
        $identifier = $this->create($identifierString, new EmptyPageProvider(), $elementName);

        if ($identifier instanceof IdentifierInterface && $parentIdentifier) {
            return $identifier->withParentIdentifier($parentIdentifier);
        }

        return $identifier;
    }

    /**
     * @param string $identifierString
     * @param PageProviderInterface $pageProvider
     * @param string|null $name
     *
     * @return IdentifierInterface|null
     *
     * @throws MalformedPageElementReferenceException
     * @throws UnknownPageException
     * @throws UnknownPageElementException
     * @throws NonRetrievablePageException
     */
    public function create(
        string $identifierString,
        PageProviderInterface $pageProvider,
        ?string $name = null
    ): ?IdentifierInterface {
        $identifierString = trim($identifierString);

        if (empty($identifierString)) {
            return null;
        }

        $type = $this->deriveType($identifierString);

        if (in_array($type, [IdentifierTypes::CSS_SELECTOR, IdentifierTypes::XPATH_EXPRESSION])) {
            list($value, $position) = $this->extractValueAndPosition($identifierString);
            $value = trim($value, '"');

            return new Identifier($type, $value, $position, $name);
        }

        if (IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE === $type) {
            $pageElementReference = new PageElementReference($identifierString);

            if (!$pageElementReference->isValid()) {
                throw new MalformedPageElementReferenceException($pageElementReference);
            }

            $importName = $pageElementReference->getImportName();

            $page = $pageProvider->findPage($importName);
            $elementName = $pageElementReference->getElementName();
            $pageElementIdentifier = $page->getElementIdentifier($elementName);

            if (null === $pageElementIdentifier) {
                throw new UnknownPageElementException($importName, $elementName);
            }

            return $pageElementIdentifier;
        }

        return new Identifier($type, $identifierString, 1, $name);
    }

    private function deriveType(string $identifierString): string
    {
        if (1 === preg_match(self::CSS_SELECTOR_REGEX, $identifierString)) {
            return IdentifierTypes::CSS_SELECTOR;
        }

        if (1 === preg_match(self::XPATH_EXPRESSION_REGEX, $identifierString)) {
            return IdentifierTypes::XPATH_EXPRESSION;
        }

        if (1 === preg_match(self::ELEMENT_PARAMETER_REGEX, $identifierString)) {
            return IdentifierTypes::ELEMENT_PARAMETER;
        }

        if (1 === preg_match(self::PAGE_OBJECT_PARAMETER_REGEX, $identifierString)) {
            return IdentifierTypes::PAGE_OBJECT_PARAMETER;
        }

        if (1 === preg_match(self::BROWSER_OBJECT_PARAMETER_REGEX, $identifierString)) {
            return IdentifierTypes::BROWSER_OBJECT_PARAMETER;
        }

        return IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE;
    }

    private function extractValueAndPosition(string $identifier)
    {
        $positionMatches = [];

        preg_match(self::POSITION_REGEX, $identifier, $positionMatches);

        $position = 1;

        if (empty($positionMatches)) {
            $quotedValue = $identifier;
        } else {
            $quotedValue = (string) preg_replace(self::POSITION_REGEX, '', $identifier);

            $positionMatch = $positionMatches[0];
            $positionString = ltrim($positionMatch, ':');

            if (self::POSITION_FIRST === $positionString) {
                $position = 1;
            } elseif (self::POSITION_LAST === $positionString) {
                $position = -1;
            } else {
                $position = (int) $positionString;
            }
        }

        return [
            $quotedValue,
            $position,
        ];
    }

    private function extractElementReferenceAndIdentifierString(string $identifier)
    {
        $elementReferenceMatches = [];
        preg_match(self::REFERENCED_ELEMENT_EXTRACTOR_REGEX, $identifier, $elementReferenceMatches);

        $elementReferencePart = $elementReferenceMatches[0];
        $identifierStringPart = trim(mb_substr($identifier, mb_strlen($elementReferencePart)));

        $elementReference = $elementReferencePart;

        if ('"' === $elementReference[0]) {
            $elementReference = ltrim($elementReference, '"');
        }

        $elementReference = trim($elementReference, '{} ');

        $identifierString = $identifierStringPart;
        $position = null;

        if (preg_match(self::POSITION_REGEX, $identifierString)) {
            list($identifierString, $position) = $this->extractValueAndPosition($identifierString);
        }

        if ('"' === $identifierString[-1] && '"' !== $identifierString[0]) {
            $identifierString = '"' . $identifierString;
        }

        if ($position) {
            $identifierString .= ':' . $position;
        }

        return [
            $elementReference,
            $identifierString
        ];
    }
}
