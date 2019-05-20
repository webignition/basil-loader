<?php

namespace webignition\BasilParser\Model\Action;

use webignition\BasilParser\Model\Value\ValueInterface;

interface InputActionInterface extends InteractionActionInterface
{
    public function getValue(): ?ValueInterface;
}
