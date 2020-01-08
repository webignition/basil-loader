<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Exception;

class EmptyTestException extends \Exception
{
    private $path;

    public function __construct(string $path)
    {
        parent::__construct(sprintf('Empty test at path "%s"', $path));

        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
