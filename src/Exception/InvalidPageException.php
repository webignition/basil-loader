<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Exception;

use webignition\BasilValidationResult\InvalidResultInterface;

class InvalidPageException extends \Exception
{
    private $importName;
    private $path;
    private $validationResult;

    public function __construct(string $importName, string $path, InvalidResultInterface $validationResult)
    {
        parent::__construct(sprintf(
            'Invalid page "%s" at path "%s": %s',
            $importName,
            $path,
            $validationResult->getReason()
        ));

        $this->importName = $importName;
        $this->path = $path;
        $this->validationResult = $validationResult;
    }

    public function getImportName(): string
    {
        return $this->importName;
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
