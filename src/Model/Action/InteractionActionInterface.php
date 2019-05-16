<?php

namespace webignition\BasilParser\Model\Action;

use webignition\BasilParser\Model\Identifier\IdentifierInterface;

interface InteractionActionInterface
{
    public function getIdentifier(): IdentifierInterface;
}
