<?php

namespace webignition\BasilParser\Model\Test;

interface ImportCollectionInterface
{
    public function getPageImportPath(string $name): ?string;
    public function getStepImportPath(string $name): ?string;
}
