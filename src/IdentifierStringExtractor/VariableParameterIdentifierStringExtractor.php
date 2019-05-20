<?php

namespace webignition\BasilParser\IdentifierStringExtractor;

class VariableParameterIdentifierStringExtractor implements IdentifierStringExtractorInterface
{
    const VARIABLE_START_CHARACTER = '$';

    public function handles(string $string): bool
    {
        return '' !== $string && self::VARIABLE_START_CHARACTER === $string[0];
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
