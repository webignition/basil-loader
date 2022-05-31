<?php

declare(strict_types=1);

namespace webignition\BasilLoader;

use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModels\Model\DataSet\DataSetCollection;
use webignition\BasilModels\Model\DataSet\DataSetCollectionInterface;

class DataSetLoader
{
    public function __construct(
        private YamlLoader $yamlLoader
    ) {
    }

    public static function createLoader(): DataSetLoader
    {
        return new DataSetLoader(
            YamlLoader::createLoader()
        );
    }

    /**
     * @throws YamlLoaderException
     */
    public function load(string $path): DataSetCollectionInterface
    {
        $data = $this->yamlLoader->loadArray($path);

        return new DataSetCollection($data);
    }
}
