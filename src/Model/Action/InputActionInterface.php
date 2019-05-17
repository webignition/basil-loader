<?php

namespace webignition\BasilParser\Model\Action;

use webignition\BasilParser\Model\Value\ValueInterface;

interface InputActionInterface
{
    public function getValue(): ValueInterface;
}
