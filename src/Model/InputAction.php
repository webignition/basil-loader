<?php

namespace webignition\BasilParser\Model;

class InputAction extends InteractionAction implements ActionInterface, InteractionActionInterface, InputActionInterface
{
    private $value = '';

    public function __construct(string $identifier, string $value)
    {
        parent::__construct(ActionTypesInterface::SET, $identifier);

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
