<?php

namespace webignition\BasilParser\IdentifierStringExtractor;

interface IdentifierStringTypeExtractorInterface
{
    public function handles(string $string): bool;
    public function extractFromStart(string $string): ?string;
}
