<?php

namespace webignition\BasilParser\Model\Action;

interface ActionInterface
{
    public function getType(): string;
    public function getArguments(): string;
    public function isRecognised(): bool;
}
