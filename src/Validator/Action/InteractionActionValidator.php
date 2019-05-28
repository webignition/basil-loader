<?php

namespace webignition\BasilParser\Validator\Action;

use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InteractionActionInterface;

class InteractionActionValidator implements ActionTypeValidatorInterface
{
    public function handles(string $type): bool
    {
        return ActionTypes::CLICK === $type || ActionTypes::SUBMIT === $type || ActionTypes::WAIT_FOR === $type;
    }

    public function validate(ActionInterface $action): bool
    {
        if ($action instanceof InteractionActionInterface) {
            return null !== $action->getIdentifier();
        }

        return false;
    }
}
