<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Loader\DataSetLoader;

class DataSetLoaderFactory
{
    public static function create(): DataSetLoader
    {
        return new DataSetLoader(
            YamlLoaderFactory::create()
        );
    }
}
