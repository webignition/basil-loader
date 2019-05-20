<?php

namespace webignition\BasilParser\IdentifierStringExtractor;

class IdentifierStringExtractor
{
    /**
     * @var IdentifierStringExtractorInterface[]
     */
    private $typeSpecificIdentifierStringExtractors = [];

    public function __construct()
    {
        $this->typeSpecificIdentifierStringExtractors[] = new QuotedIdentifierStringExtractor();
        $this->typeSpecificIdentifierStringExtractors[] = new VariableParameterIdentifierStringExtractor();
        $this->typeSpecificIdentifierStringExtractors[] = new LiteralParameterIdentifierStringExtractor();
    }

    public function extractFromStart(string $string): string
    {
        $typeSpecificIdentifierStringExtractor = $this->findTypeSpecificIdentifierStringExtractor($string);

        if ($typeSpecificIdentifierStringExtractor instanceof IdentifierStringExtractorInterface) {
            return (string) $typeSpecificIdentifierStringExtractor->extractFromStart($string);
        }

        return '';
    }

    private function findTypeSpecificIdentifierStringExtractor(string $string): ?IdentifierStringExtractorInterface
    {
        foreach ($this->typeSpecificIdentifierStringExtractors as $typeSpecificIdentifierStringExtractor) {
            if ($typeSpecificIdentifierStringExtractor->handles($string)) {
                return $typeSpecificIdentifierStringExtractor;
            }
        }

        return null;
    }
}
