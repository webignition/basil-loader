<?php

namespace webignition\BasilParser\Model\Action;

use webignition\BasilParser\Model\Identifier\IdentifierInterface;

class InteractionAction extends Action implements ActionInterface, InteractionActionInterface
{
    private $identifier;

    public function __construct(string $verb, IdentifierInterface $identifier)
    {
        parent::__construct($verb);

        $this->identifier = $identifier;
    }

    public function getIdentifier(): IdentifierInterface
    {
        return $this->identifier;
    }
}
