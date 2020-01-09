<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Exception;

use webignition\BasilParser\Exception\UnparseableDataExceptionInterface;
use webignition\BasilParser\Exception\UnparseableStepException;
use webignition\BasilParser\Exception\UnparseableTestException;

class ParseException extends \Exception
{
    private $path;
    private $unparseableDataException;

    public function __construct(string $path, UnparseableDataExceptionInterface $unparseableDataException)
    {
        parent::__construct(
            sprintf('Parse error when loading: %s', $unparseableDataException->getMessage()),
            0,
            $unparseableDataException
        );

        $this->path = $path;
        $this->unparseableDataException = $unparseableDataException;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getUnparseableDataException(): UnparseableDataExceptionInterface
    {
        return $this->unparseableDataException;
    }

    public function isUnparseableTestException(): bool
    {
        return $this->unparseableDataException instanceof UnparseableTestException;
    }

    public function isUnparseableStepException(): bool
    {
        return $this->unparseableDataException instanceof UnparseableStepException;
    }
}
