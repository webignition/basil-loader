<?php

namespace webignition\BasilParser\Model\Action;

use webignition\BasilParser\Model\Identifier\IdentifierInterface;

interface InteractionActionInterface extends ActionInterface
{
    public function getIdentifier(): ?IdentifierInterface;
}
