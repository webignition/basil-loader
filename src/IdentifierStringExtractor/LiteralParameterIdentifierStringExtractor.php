<?php

namespace webignition\BasilParser\IdentifierStringExtractor;

class LiteralParameterIdentifierStringExtractor implements IdentifierStringExtractorInterface
{
    const VARIABLE_START_CHARACTER = '$';

    public function handles(string $string): bool
    {
        $firstCharacter = $string[0];

        return $firstCharacter !== '"' && $firstCharacter !== '$';
    }

    public function extractFromStart(string $string): ?string
    {
        if (!$this->handles($string)) {
            return null;
        }

        $spacePosition = mb_strpos($string, ' ');

        if (false === $spacePosition) {
            return $string;
        }

        return mb_substr($string, 0, $spacePosition);
    }
}
