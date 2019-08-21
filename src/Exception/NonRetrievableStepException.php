<?php

namespace webignition\BasilLoader\Exception;

class NonRetrievableStepException extends AbstractNonRetrievableImportException
{
    public function __construct(string $importName, string $importPath, \Throwable $previous)
    {
        parent::__construct(
            $importName,
            $importPath,
            'Cannot retrieve step "' . $importName . '" from "' . $importPath . '"',
            $previous
        );
    }
}
