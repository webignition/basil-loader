<?php

namespace webignition\BasilParser\Validator\Action;

use webignition\BasilParser\Model\Action\ActionInterface;

interface ActionValidatorInterface
{
    public function handles(string $type): bool;
    public function validate(ActionInterface $action): bool;
}
