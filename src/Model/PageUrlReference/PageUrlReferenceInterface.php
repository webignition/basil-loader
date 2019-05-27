<?php

namespace webignition\BasilParser\Model\PageUrlReference;

interface PageUrlReferenceInterface
{
    public function getImportName(): string;
    public function isValid(): bool;
    public function __toString(): string;
}
