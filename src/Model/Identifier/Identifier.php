<?php

namespace webignition\BasilParser\Model\Identifier;

class Identifier implements IdentifierInterface
{
    const DEFAULT_POSITION = 1;

    private $type = '';
    private $value = '';
    private $position = 1;
    private $elementReference = null;

    public function __construct(string $type, string $value, int $position = null)
    {
        $position = $position ?? self::DEFAULT_POSITION;

        $this->type = $type;
        $this->value = $value;
        $this->position = $position;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getElementReference(): ?string
    {
        return $this->elementReference;
    }

    public function withElementReference(string $elementReference): IdentifierInterface
    {
        $new = clone $this;
        $new->elementReference = $elementReference;

        return $new;
    }

    public function __toString(): string
    {
        $value = $this->value;

        if ($this->elementReference) {
            $value = '{{ ' . $this->elementReference . ' }} ' . $value;
        }

        $string = in_array($this->type, [IdentifierTypes::CSS_SELECTOR, IdentifierTypes::XPATH_EXPRESSION])
            ? '"' . $value . '"'
            : $value;

        if (self::DEFAULT_POSITION !== $this->position) {
            $string .= ':' . $this->position;
        }

        return $string;
    }
}
