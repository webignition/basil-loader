<?php

namespace webignition\BasilParser\IdentifierStringExtractor;

class QuotedIdentifierStringExtractor implements IdentifierStringExtractorInterface
{
    const DELIMITER = '"';
    const ESCAPED_DELIMITER = '\"';

    public function handles(string $string): bool
    {
        return self::DELIMITER === $string[0];
    }

    public function extractFromStart(string $string): ?string
    {
        if (!$this->handles($string)) {
            return null;
        }

        $currentQuotePosition = 0;
        $endingQuotePosition = null;

        while (null === $endingQuotePosition) {
            $nextQuotePosition = mb_strpos($string, self::DELIMITER, $currentQuotePosition + 1);

            if (mb_substr($string, $nextQuotePosition -1, 2) !== self::ESCAPED_DELIMITER) {
                $endingQuotePosition = $nextQuotePosition;
            } else {
                $currentQuotePosition = mb_strpos($string, self::DELIMITER, $nextQuotePosition + 1);
            }
        }

        return mb_substr($string, 0, $endingQuotePosition + 1);
    }
}
