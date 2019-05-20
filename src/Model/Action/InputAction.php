<?php

namespace webignition\BasilParser\Model\Action;

use webignition\BasilParser\Model\Identifier\IdentifierInterface;
use webignition\BasilParser\Model\Value\ValueInterface;

class InputAction extends InteractionAction implements InputActionInterface
{
    private $value;

    public function __construct(IdentifierInterface $identifier, ?ValueInterface $value, string $arguments)
    {
        parent::__construct(ActionTypes::SET, $identifier, $arguments);

        $this->value = $value;
    }

    public function getValue(): ?ValueInterface
    {
        return $this->value;
    }
}
