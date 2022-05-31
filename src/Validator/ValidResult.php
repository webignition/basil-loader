<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Validator;

class ValidResult extends AbstractResult
{
    public function __construct(mixed $subject)
    {
        parent::__construct(true, $subject);
    }
}
