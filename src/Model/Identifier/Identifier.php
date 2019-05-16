<?php

namespace webignition\BasilParser\Model\Identifier;

class Identifier implements IdentifierInterface
{
    const DEFAULT_POSITION = 1;

    private $type = '';
    private $value = '';
    private $position = 1;

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
}
