<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Exception;

class UnknownTestException extends AbstractUnknownImportException
{
    public function __construct(string $importName)
    {
        parent::__construct($importName, 'Unknown test "' . $importName . '"');
    }
}
