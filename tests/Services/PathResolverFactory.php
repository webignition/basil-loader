<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\PathResolver\PathResolver;

class PathResolverFactory
{
    public static function create(): PathResolver
    {
        return new PathResolver();
    }
}
