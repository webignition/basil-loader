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

        return (string) trim(implode($identifierMatches[0], ''));
    }

    private function createStopStringsPattern(array $stopStrings): string
    {
        $stopStringsPatternParts = [];

        foreach ($stopStrings as $stopString) {
            $stopStringsPatternParts[] = '(' . $stopString . ')';
        }

        return implode('|', $stopStringsPatternParts);
    }
}
