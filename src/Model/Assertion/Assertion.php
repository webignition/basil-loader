<?php

namespace webignition\BasilParser\Model\Assertion;

use webignition\BasilParser\Model\Identifier\IdentifierInterface;
use webignition\BasilParser\Model\Value\ValueInterface;

class Assertion implements AssertionInterface
{
    private $identifier;
    private $comparison;
    private $value;

    public function __construct(
        IdentifierInterface $identifier,
        string $comparison,
        ?ValueInterface $value = null
    ) {
        $this->identifier = $identifier;
        $this->comparison = $comparison;
        $this->value = $value;
    }

    public function getIdentifier(): IdentifierInterface
    {
        return $this->identifier;
    }

    public function getComparison(): string
    {
        return $this->comparison;
    }

    public function getValue(): ?ValueInterface
    {
        return $this->value;
    }
}
