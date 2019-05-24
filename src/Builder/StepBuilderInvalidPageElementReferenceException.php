<?php

namespace webignition\BasilParser\Builder;

class StepBuilderInvalidPageElementReferenceException extends \Exception
{
    private $stepName;
    private $pageElementReference;
    public function __construct(string $stepName, string $pageElementReference)
    {
        parent::__construct(
            'Invalid page element reference "' . $pageElementReference . '" in step "' . $stepName . '"'
        );

        $this->stepName = $stepName;
        $this->pageElementReference = $pageElementReference;
    }
}
