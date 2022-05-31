<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Validator;

interface InvalidResultInterface extends ResultInterface
{
    public function getType(): string;

    public function getReason(): string;

    public function getPrevious(): ?InvalidResultInterface;

    /**
     * @param array<mixed> $context
     */
    public function withContext(array $context): InvalidResultInterface;

    /**
     * @return array<mixed>
     */
    public function getContext(): array;
}
