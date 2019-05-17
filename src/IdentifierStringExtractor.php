<?php

namespace webignition\BasilParser;

class IdentifierStringExtractor
{
    const IDENTIFIER_REGEX =
        '/.+?(?=(( is )|( is-not )|( exists)|( not-exists)|( includes )|( excludes )|( matches )))/';

    public function extractFromStart(string $string): string
    {
        $identifierMatches = [];
        preg_match_all(self::IDENTIFIER_REGEX, $string, $identifierMatches);

        return implode($identifierMatches[0], '');
    }
}
