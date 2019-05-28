<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\IdentifierStringExtractor\IdentifierStringExtractor;
use webignition\BasilParser\IdentifierStringExtractor\LiteralParameterIdentifierStringExtractor;
use webignition\BasilParser\IdentifierStringExtractor\QuotedIdentifierStringExtractor;
use webignition\BasilParser\IdentifierStringExtractor\VariableParameterIdentifierStringExtractor;

class IdentifierStringExtractorFactory
{
    public static function create(): IdentifierStringExtractor
    {
        $identifierStringExtractor = new IdentifierStringExtractor();

        $identifierStringExtractor->addIdentifierStringTypeExtractor(new QuotedIdentifierStringExtractor());
        $identifierStringExtractor->addIdentifierStringTypeExtractor(new VariableParameterIdentifierStringExtractor());
        $identifierStringExtractor->addIdentifierStringTypeExtractor(new LiteralParameterIdentifierStringExtractor());

        return $identifierStringExtractor;
    }
}
