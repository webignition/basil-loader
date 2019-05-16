<?php

namespace webignition\BasilParser\Model\Action;

class InputAction extends InteractionAction implements ActionInterface, InteractionActionInterface, InputActionInterface
{
    private $value = '';

    public function __construct(string $identifier, string $value)
    {
        parent::__construct(TypesInterface::SET, $identifier);

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
