<?php

namespace webignition\BasilParser\Model;

class InteractionAction extends Action implements ActionInterface, InteractionActionInterface
{
    private $identifier = '';

    public function __construct(string $verb, string $identifier)
    {
        parent::__construct($verb);

        $this->identifier = $identifier;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
