<?php

namespace webignition\BasilParser\Model\Test;

use Psr\Http\Message\UriInterface;

interface ConfigurationInterface
{
    public function getBrowser(): string;
    public function getUrl(): UriInterface;
}
