<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Exception;

class NonRetrievableDataProviderException extends AbstractNonRetrievableImportException
{
    public function __construct(string $importName, string $importPath, \Throwable $previous)
    {
        parent::__construct(
            $importName,
            $importPath,
            'Cannot retrieve data provider "' . $importName . '" from "' . $importPath . '"',
            $previous
        );
    }
}
