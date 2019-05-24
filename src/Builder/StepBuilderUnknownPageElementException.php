<?php

namespace webignition\BasilParser\Builder;

class StepBuilderUnknownPageElementException extends \Exception
{
    private $stepName;
    private $importName;
    private $elementName;
    private $elementNames;

    public function __construct(string $stepName, string $importName, string $elementName, array $elementNames)
    {
        parent::__construct(
            'Unknown page element "' . $elementName . '" in page "' . $importName . '" in step "' . $stepName . '"'
        );

        $this->stepName = $stepName;
        $this->importName = $importName;
        $this->elementName = $elementName;
        $this->elementNames = $elementNames;
    }
}
