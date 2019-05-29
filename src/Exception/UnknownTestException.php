<?php

namespace webignition\BasilParser\Exception;

class UnknownTestException extends AbstractUnknownImportException
{
    public function __construct(string $importName)
    {
        parent::__construct($importName, 'Unknown test"' . $importName . '"');
    }
}
