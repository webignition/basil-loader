<?php

namespace webignition\BasilParser\Exception;

class CircularStepImportException extends \Exception
{
    const MESSAGE = 'Circular step import "%s"';

    private $importName = '';

    public function __construct(string $importName)
    {
        $this->importName = $importName;

        parent::__construct(sprintf(self::MESSAGE, $importName));
    }

    public function getImportName(): string
    {
        return $this->importName;
    }
}
