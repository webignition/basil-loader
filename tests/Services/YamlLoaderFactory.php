<?php

namespace webignition\BasilParser\Tests\Services;

use Symfony\Component\Yaml\Parser as YamlParser;
use webignition\BasilParser\Loader\YamlLoader;

class YamlLoaderFactory
{
    public static function create(): YamlLoader
    {
        return new YamlLoader(
            new YamlParser()
        );
    }
}
