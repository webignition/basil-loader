<?php

namespace webignition\BasilParser\Validator\Action;

use webignition\BasilModel\Action\ActionInterface;

interface ActionTypeValidatorInterface
{
    public function handles(string $type): bool;
    public function validate(ActionInterface $action): bool;
}
