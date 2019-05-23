<?php

namespace webignition\BasilParser\Builder;

class UnknownPageImportException extends \Exception
{
    private $stepName;
    private $importName;
    private $pageImportPaths;

    public function __construct(string $stepName, string $importName, array $pageImportPaths)
    {
        parent::__construct(
            'Unknown page import "' . $importName . '" in step "' . $stepName . '"'
        );

        $this->stepName = $stepName;
        $this->importName = $importName;
        $this->pageImportPaths = $pageImportPaths;
    }
}
