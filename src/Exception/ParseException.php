<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Exception;

use webignition\BasilParser\Exception\UnparseableDataExceptionInterface;
use webignition\BasilParser\Exception\UnparseableStepException;
use webignition\BasilParser\Exception\UnparseableTestException;

class ParseException extends \Exception
{
    private string $testPath;
    private string $subjectPath;
    private UnparseableDataExceptionInterface $unparseableDataException;

    public function __construct(
        string $testPath,
        string $path,
        UnparseableDataExceptionInterface $unparseableDataException
    ) {
        parent::__construct(
            sprintf('Parse error when loading: %s', $unparseableDataException->getMessage()),
            0,
            $unparseableDataException
        );

        $this->testPath = $testPath;
        $this->subjectPath = $path;
        $this->unparseableDataException = $unparseableDataException;
    }

    public function getTestPath(): string
    {
        return $this->testPath;
    }

    public function getSubjectPath(): string
    {
        return $this->subjectPath;
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
