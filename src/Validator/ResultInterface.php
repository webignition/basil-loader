<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Validator;

interface ResultInterface
{
    public function getIsValid(): bool;

    public function getSubject(): mixed;
}
