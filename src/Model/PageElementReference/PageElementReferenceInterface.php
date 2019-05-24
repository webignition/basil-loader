<?php

namespace webignition\BasilParser\Model\PageElementReference;

interface PageElementReferenceInterface
{
    public function getImportName(): string;
    public function getElementName(): string;
    public function isValid(): bool;
    public function __toString(): string;
}
