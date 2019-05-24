<?php

namespace webignition\BasilParser\Builder;

class StepBuilderUnknownStepImportException extends \Exception
{
    private $stepName;
    private $importName;
    private $stepImportPaths;

    public function __construct(string $stepName, string $importName, array $stepImportPaths)
    {
        parent::__construct(
            'Unknown step import "' . $importName . '" in step "' . $stepName . '"'
        );

        $this->stepName = $stepName;
        $this->importName = $importName;
        $this->stepImportPaths = $stepImportPaths;
    }
}
