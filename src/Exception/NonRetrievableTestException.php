<?php

namespace webignition\BasilLoader\Exception;

class NonRetrievableTestException extends AbstractNonRetrievableImportException
{
    public function __construct(string $importPath, \Throwable $previous)
    {
        parent::__construct(
            '',
            $importPath,
            'Cannot retrieve test from "' . $importPath . '"',
            $previous
        );
    }
}
