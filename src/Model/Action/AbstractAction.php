<?php

namespace webignition\BasilParser\Model\Action;

abstract class AbstractAction implements ActionInterface
{
    private $type = '';
    private $isRecognised = false;

    public function __construct(string $type, bool $isRecognised = false)
    {
        $this->type = $type;
        $this->isRecognised = $isRecognised;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isRecognised(): bool
    {
        return $this->isRecognised;
    }
}
