<?php

namespace webignition\BasilParser\Validator\Action;

use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Action\ActionTypes;

class NoArgumentsActionValidator implements ActionValidatorInterface
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
