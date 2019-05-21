<?php

namespace webignition\BasilParser\Model\Page;

use Psr\Http\Message\UriInterface;
use webignition\BasilParser\Model\Identifier\IdentifierInterface;

interface PageInterface
{
    public function getUri(): UriInterface;
    public function getElementIdentifier(string $name): ?IdentifierInterface;
}
