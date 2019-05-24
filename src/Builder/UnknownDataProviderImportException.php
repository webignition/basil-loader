<?php

namespace webignition\BasilParser\Builder;

class UnknownDataProviderImportException extends \Exception
{
    private $stepName;
    private $importName;
    private $dataProviderImportPaths;

    public function __construct(string $stepName, string $importName, array $dataProviderImportPaths)
    {
        parent::__construct(
            'Unknown data provider import "' . $importName . '" in step "' . $stepName . '"'
        );

        $this->stepName = $stepName;
        $this->importName = $importName;
        $this->dataProviderImportPaths = $dataProviderImportPaths;
    }
}
