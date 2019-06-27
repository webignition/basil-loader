<?php

namespace webignition\BasilParser\Validator\Action;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\InputActionInterface;

class InputActionValidator implements ActionTypeValidatorInterface
{
    const IDENTIFIER_STOP_WORD = ' to ';

    public function handles(string $type): bool
    {
        return ActionTypes::SET === $type;
    }

    public function validate(ActionInterface $action): bool
    {
        if ($action instanceof InputActionInterface) {
            if (null === $action->getIdentifier()) {
                return false;
            }

            if (null === $action->getValue()) {
                return false;
            }

            return $this->hasToKeyword($action);
        }

        return false;
    }

    private function hasToKeyword(InputActionInterface $action): bool
    {
        $arguments = $action->getArguments();

        if (mb_substr_count($arguments, self::IDENTIFIER_STOP_WORD) === 0) {
            return false;
        }

        $argumentsWithoutSelector = mb_substr($arguments, mb_strlen((string) $action->getIdentifier()));

        $stopWord = self::IDENTIFIER_STOP_WORD;
        return mb_substr($argumentsWithoutSelector, 0, strlen($stopWord)) === $stopWord;
    }
}
