<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Exception;

use webignition\BasilValidationResult\InvalidResultInterface;

class InvalidTestException extends \Exception
{
    private string $path;
    private InvalidResultInterface $validationResult;

    public function __construct(string $path, InvalidResultInterface $validationResult)
    {
        parent::__construct(sprintf(
            'Invalid test at path "%s": %s',
            $path,
            $validationResult->getReason()
        ));

        $this->path = $path;
        $this->validationResult = $validationResult;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getValidationResult(): InvalidResultInterface
    {
        return $this->validationResult;
    }
}
