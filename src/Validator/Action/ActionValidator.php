<?php

namespace webignition\BasilParser\Validator\Action;

use webignition\BasilParser\Model\Action\ActionInterface;

class ActionValidator implements ActionValidatorInterface
{
    /**
     * @var ActionValidatorInterface[]
     */
    private $typeSpecificActionValidators;

    public function __construct()
    {
        $this->typeSpecificActionValidators[] = new InputActionValidator();
        $this->typeSpecificActionValidators[] = new InteractionActionValidator();
        $this->typeSpecificActionValidators[] = new NoArgumentsActionValidator();
        $this->typeSpecificActionValidators[] = new WaitActionValidator();
    }

    public function handles(string $type): bool
    {
        return true;
    }

    public function validate(ActionInterface $action): bool
    {
        $typeSpecificActionValidator = $this->findTypeSpecificActionValidator($action->getType());

        return null == $typeSpecificActionValidator
            ? false
            : $typeSpecificActionValidator->validate($action);
    }

    private function findTypeSpecificActionValidator(string $type): ?ActionValidatorInterface
    {
        foreach ($this->typeSpecificActionValidators as $typeSpecificActionValidator) {
            if ($typeSpecificActionValidator->handles($type)) {
                return $typeSpecificActionValidator;
            }
        }

        return null;
    }
}
