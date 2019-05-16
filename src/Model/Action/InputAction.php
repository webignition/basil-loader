<?php

namespace webignition\BasilParser\Model\Action;

use webignition\BasilParser\Model\Identifier\IdentifierInterface;

class InputAction extends InteractionAction implements ActionInterface, InteractionActionInterface, InputActionInterface
{
    private $value = '';

    public function __construct(IdentifierInterface $identifier, string $value)
    {
        parent::__construct(ActionTypesInterface::SET, $identifier);

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
