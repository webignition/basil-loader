<?php

namespace webignition\BasilParser\IdentifierStringExtractor;

class IdentifierStringExtractor
{
    const IDENTIFIER_REGEX = '/.+?(?=%s)/';
    const DOM_EXPRESSION_FIRST_CHARACTER = '"';

    /**
     * @var IdentifierStringExtractorInterface[]
     */
    private $typeSpecificIdentifierStringExtractors = [];

    /**
     * @var LiteralParameterIdentifierStringExtractor
     */
    private $literalParameterIdentifierStringExtractor;

    public function __construct(array $typeSpecificIdentifierStringExtractors = [])
    {
        foreach ($typeSpecificIdentifierStringExtractors as $typeSpecificIdentifierStringExtractor) {
            if ($typeSpecificIdentifierStringExtractor instanceof IdentifierStringExtractorInterface) {
                $this->typeSpecificIdentifierStringExtractors[] = $typeSpecificIdentifierStringExtractor;
            }
        }

        $this->literalParameterIdentifierStringExtractor = new LiteralParameterIdentifierStringExtractor();
    }

    public static function create()
    {
        return new IdentifierStringExtractor([
            new QuotedIdentifierStringExtractor(),
            new VariableParameterIdentifierStringExtractor(),
        ]);
    }

    public function extractFromStart(string $string): string
    {
        $typeSpecificIdentifierStringExtractor = $this->findTypeSpecificIdentifierStringExtractor($string);

        return $typeSpecificIdentifierStringExtractor->extractFromStart($string);
    }

    private function findTypeSpecificIdentifierStringExtractor(string $string): IdentifierStringExtractorInterface
    {
        foreach ($this->typeSpecificIdentifierStringExtractors as $typeSpecificIdentifierStringExtractor) {
            if ($typeSpecificIdentifierStringExtractor->handles($string)) {
                return $typeSpecificIdentifierStringExtractor;
            }
        }

        return $this->literalParameterIdentifierStringExtractor;
    }
}
