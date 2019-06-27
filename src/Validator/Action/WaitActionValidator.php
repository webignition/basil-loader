<?php

namespace webignition\BasilParser\Validator\Action;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\WaitActionInterface;

class WaitActionValidator implements ActionTypeValidatorInterface
{
    public function handles(string $type): bool
    {
        return ActionTypes::WAIT === $type;
    }

    public function validate(ActionInterface $action): bool
    {
        if ($action instanceof WaitActionInterface) {
            return !empty($action->getDuration());
        }

        return false;
    }
}
