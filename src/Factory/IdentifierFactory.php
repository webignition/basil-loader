<?php

namespace webignition\BasilParser\Factory;

use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierInterface;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;

class IdentifierFactory
{
    const POSITION_FIRST = 'first';
    const POSITION_LAST = 'last';

    const POSITION_PATTERN = ':(-?[0-9]+|first|last)';
    const POSITION_REGEX = '/' . self::POSITION_PATTERN . '$/';
    const CSS_SELECTOR_REGEX = '/^"((?!\/).).+("|' . self::POSITION_PATTERN . ')$/';
    const XPATH_EXPRESSION_REGEX = '/^"\/.+("|' . self::POSITION_PATTERN . ')$/';
    const ELEMENT_PARAMETER_REGEX = '/^\$.+/';
    const REFERENCED_ELEMENT_REGEX = '/^"{{.+/';
    const REFERENCED_ELEMENT_EXTRACTOR_REGEX = '/^".+?(?=(}}))}}/';

    public function createWithElementReference(
        string $identifierString,
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
        $identifier = $this->create($identifierString);

        if ($identifier instanceof IdentifierInterface && $parentIdentifier) {
            return $identifier->withParentIdentifier($parentIdentifier);
        }

        return $identifier;
    }

    public function create(string $identifierString, ?string $name = null): ?IdentifierInterface
    {
        $identifierString = trim($identifierString);

        if (empty($identifierString)) {
            return null;
        }

        if (1 === preg_match(self::CSS_SELECTOR_REGEX, $identifierString)) {
            list($value, $position) = $this->extractValueAndPosition($identifierString);
            $value = trim($value, '"');

            return new Identifier(IdentifierTypes::CSS_SELECTOR, $value, $position, $name);
        }

        if (1 === preg_match(self::XPATH_EXPRESSION_REGEX, $identifierString)) {
            list($value, $position) = $this->extractValueAndPosition($identifierString);
            $value = trim($value, '"');

            return new Identifier(IdentifierTypes::XPATH_EXPRESSION, $value, $position, $name);
        }

        if (1 === preg_match(self::ELEMENT_PARAMETER_REGEX, $identifierString)) {
            return new Identifier(IdentifierTypes::ELEMENT_PARAMETER, $identifierString, 1, $name);
        }

        return new Identifier(IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE, $identifierString, 1, $name);
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
