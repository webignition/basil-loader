<?php

namespace webignition\BasilParser\Factory;

use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierInterface;
use webignition\BasilParser\Model\Identifier\IdentifierTypesInterface;

class IdentifierFactory
{
    const POSITION_FIRST = 'first';
    const POSITION_LAST = 'last';

    const POSITION_PATTERN = ':(-?[0-9]+|first|last)';
    const POSITION_REGEX = '/' . self::POSITION_PATTERN . '$/';
    const CSS_SELECTOR_REGEX = '/^"((?!\/).).+("|' . self::POSITION_PATTERN . ')$/';
    const XPATH_EXPRESSION_REGEX = '/^"\/.+("|' . self::POSITION_PATTERN . ')$/';
    const ELEMENT_PARAMETER_REGEX = '/^\$.+/';

    public function create(string $identifier): IdentifierInterface
    {
        if (1 === preg_match(self::CSS_SELECTOR_REGEX, $identifier)) {
            list($value, $position) = $this->extractValueAndPosition($identifier);

            return new Identifier(IdentifierTypesInterface::CSS_SELECTOR, $value, $position);
        }

        if (1 === preg_match(self::XPATH_EXPRESSION_REGEX, $identifier)) {
            list($value, $position) = $this->extractValueAndPosition($identifier);

            return new Identifier(IdentifierTypesInterface::XPATH_EXPRESSION, $value, $position);
        }

        if (1 === preg_match(self::ELEMENT_PARAMETER_REGEX, $identifier)) {
            return new Identifier(IdentifierTypesInterface::ELEMENT_PARAMETER, $identifier, 1);
        }

        return new Identifier(IdentifierTypesInterface::PAGE_MODEL_ELEMENT_REFERENCE, $identifier, 1);
    }

    private function extractValueAndPosition(string $identifier)
    {
        $positionMatches = [];

        preg_match(self::POSITION_REGEX, $identifier, $positionMatches);

        $position = 1;

        if (empty($positionMatches)) {
            $quotedValue = $identifier;
        } else {
            $quotedValue = preg_replace(self::POSITION_REGEX, '', $identifier);

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

        $value = trim($quotedValue, '""');

        return [
            $value,
            $position,
        ];
    }
}
