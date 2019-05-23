<?php

namespace webignition\BasilParser\Model\Test;

interface ImportCollectionInterface
{
    public function getImportPath(string $name): ?string;
}
