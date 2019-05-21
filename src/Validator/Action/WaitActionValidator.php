<?php

namespace webignition\BasilParser\Validator\Action;

use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\WaitActionInterface;

class WaitActionValidator implements ActionValidatorInterface
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
