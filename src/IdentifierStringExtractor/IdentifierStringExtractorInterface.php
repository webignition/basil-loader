<?php

namespace webignition\BasilParser\IdentifierStringExtractor;

interface IdentifierStringExtractorInterface
{
    public function handles(string $string): bool;
    public function extractFromStart(string $string): ?string;
}
