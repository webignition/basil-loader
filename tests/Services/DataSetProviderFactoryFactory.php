<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Loader\DataSetLoader;
use webignition\BasilParser\Provider\DataSet\Factory as DataSetProviderFactory;

class DataSetProviderFactoryFactory
{
    public static function create(): DataSetProviderFactory
    {
        return new DataSetProviderFactory(
            DataSetLoader::createLoader()
        );
    }
}
