<?php

namespace webignition\BasilParser\IdentifierStringExtractor;

class IdentifierStringExtractor
{
    /**
     * @var IdentifierStringTypeExtractorInterface[]
     */
    private $identifierStringTypeExtractors = [];

    public function addIdentifierStringTypeExtractor(
        IdentifierStringTypeExtractorInterface $identifierStringTypeExtractor
    ) {
        $this->identifierStringTypeExtractors[] = $identifierStringTypeExtractor;
    }

    public function extractFromStart(string $string): string
    {
        $typeSpecificIdentifierStringExtractor = $this->findTypeSpecificIdentifierStringExtractor($string);

        if ($typeSpecificIdentifierStringExtractor instanceof IdentifierStringTypeExtractorInterface) {
            return (string) $typeSpecificIdentifierStringExtractor->extractFromStart($string);
        }

        return '';
    }

    private function findTypeSpecificIdentifierStringExtractor(string $string): ?IdentifierStringTypeExtractorInterface
    {
        foreach ($this->identifierStringTypeExtractors as $typeSpecificIdentifierStringExtractor) {
            if ($typeSpecificIdentifierStringExtractor->handles($string)) {
                return $typeSpecificIdentifierStringExtractor;
            }
        }

        return null;
    }
}
