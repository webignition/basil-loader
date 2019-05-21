<?php

namespace webignition\BasilParser\Model\Identifier;

interface IdentifierInterface
{
    public function getType(): string;
    public function getValue(): string;
    public function getPosition(): int;
    public function getElementReference(): ?string;
    public function withElementReference(string $elementReference): IdentifierInterface;
    public function __toString(): string;
}
