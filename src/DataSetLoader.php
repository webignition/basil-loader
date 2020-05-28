<?php

declare(strict_types=1);

namespace webignition\BasilLoader;

use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModels\DataSet\DataSetCollection;
use webignition\BasilModels\DataSet\DataSetCollectionInterface;

class DataSetLoader
{
    private YamlLoader $yamlLoader;

    public function __construct(YamlLoader $yamlLoader)
    {
        $this->yamlLoader = $yamlLoader;
    }

    public static function createLoader(): DataSetLoader
    {
        return new DataSetLoader(
            YamlLoader::createLoader()
        );
    }

    /**
     * @param string $path
     *
     * @return DataSetCollectionInterface
     *
     * @throws YamlLoaderException
     */
    public function load(string $path): DataSetCollectionInterface
    {
        $data = $this->yamlLoader->loadArray($path);

        return new DataSetCollection($data);
    }
}
