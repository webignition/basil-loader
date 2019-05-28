<?php

namespace webignition\BasilParser\Validator\Action;

use webignition\BasilParser\Model\Action\ActionInterface;

class ActionValidator
{
    /**
     * @var ActionTypeValidatorInterface[]
     */
    private $actionTypeValidators = [];

    public function addActionTypeValidator(ActionTypeValidatorInterface $actionTypeValidator)
    {
        $this->actionTypeValidators[] = $actionTypeValidator;
    }

    public function validate(ActionInterface $action): bool
    {
        $typeSpecificActionValidator = $this->findTypeSpecificActionValidator($action->getType());

        return null == $typeSpecificActionValidator
            ? false
            : $typeSpecificActionValidator->validate($action);
    }

    private function findTypeSpecificActionValidator(string $type): ?ActionTypeValidatorInterface
    {
        foreach ($this->actionTypeValidators as $typeSpecificActionValidator) {
            if ($typeSpecificActionValidator->handles($type)) {
                return $typeSpecificActionValidator;
            }
        }

        return null;
    }
}
