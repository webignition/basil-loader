<?php

namespace webignition\BasilParser\Validator\Action;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Action\ActionTypes;

class NoArgumentsActionValidator implements ActionTypeValidatorInterface
{
    public function handles(string $type): bool
    {
        return ActionTypes::RELOAD === $type || ActionTypes::BACK === $type || ActionTypes::FORWARD === $type;
    }

    public function validate(ActionInterface $action): bool
    {
        return $this->handles($action->getType());
    }
}
