<?php

namespace webignition\BasilLoader\Exception;

class NonRetrievablePageException extends AbstractNonRetrievableImportException
{
    public function __construct(string $importName, string $importPath, \Throwable $previous)
    {
        parent::__construct(
            $importName,
            $importPath,
            'Cannot retrieve page "' . $importName . '" from "' . $importPath . '"',
            $previous
        );
    }
}
