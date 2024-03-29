<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Validator;

class InvalidResult extends AbstractResult implements InvalidResultInterface
{
    public const TYPE_UNHANDLED = 'unhandled';

    /**
     * @var array<mixed>
     */
    private array $context = [];

    public function __construct(
        mixed $subject,
        private string $type,
        private string $reason,
        private ?InvalidResultInterface $previous = null
    ) {
        parent::__construct(false, $subject);
    }

    public static function createUnhandledSubjectResult(mixed $subject): InvalidResultInterface
    {
        return new InvalidResult($subject, self::TYPE_UNHANDLED, '');
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getPrevious(): ?InvalidResultInterface
    {
        return $this->previous;
    }

    public function withContext(array $context): InvalidResultInterface
    {
        $new = clone $this;
        $new->context = $context;

        return $new;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
