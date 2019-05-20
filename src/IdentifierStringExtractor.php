<?php

namespace webignition\BasilParser;

class IdentifierStringExtractor
{
    const IDENTIFIER_REGEX = '/.+?(?=%s)/';

    public function extractFromStart(string $string, array $stopStrings = []): string
    {
        $stopStringsPattern = $this->createStopStringsPattern($stopStrings);
        $identifierRegex = sprintf(self::IDENTIFIER_REGEX, $stopStringsPattern);

        $identifierMatches = [];
        preg_match_all($identifierRegex, $string, $identifierMatches);

        $identifierString = (string) trim(implode($identifierMatches[0], ''));

        if ('' === $identifierString) {
            $identifierString = $this->resolveEmptyIdentifierStringForStopStrings($string, $stopStrings);
        }

        return $identifierString;
    }

    private function createStopStringsPattern(array $stopStrings): string
    {
        $stopStringsPatternParts = [];

        foreach ($stopStrings as $stopString) {
            $stopStringsPatternParts[] = '(' . $stopString . ')';
        }

        return implode('|', $stopStringsPatternParts);
    }

    private function resolveEmptyIdentifierStringForStopStrings(string $string, array $stopStrings): string
    {
        foreach ($stopStrings as $stopString) {
            $resolvedIdentifierString = $this->resolveEmptyIdentifierStringForStopString($string, $stopString);

            if ($resolvedIdentifierString !== $string) {
                return $resolvedIdentifierString;
            }
        }

        return $string;
    }

    private function resolveEmptyIdentifierStringForStopString(string $string, string $stopString): string
    {
        $trimmedStopString = trim($stopString);
        $endsWithStopStringRegex = '/(( ' . $trimmedStopString . ' )|( ' . $trimmedStopString . '))$/';
        $identifierString = $string;

        if (preg_match($endsWithStopStringRegex, $string) > 0) {
            $identifierString = (string) preg_replace($endsWithStopStringRegex, '', $string);
        }

        return $identifierString;
    }
}
