<?php

namespace webignition\BasilLoader;

use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModel\DataSet\DataSet;
use webignition\BasilModel\DataSet\DataSetCollection;
use webignition\BasilModel\DataSet\DataSetCollectionInterface;

class DataSetLoader
{
    private $yamlLoader;

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

        $dataSetCollection = new DataSetCollection();

        foreach ($data as $dataSetName => $dataSetData) {
            if (is_array($dataSetData)) {
                $dataSetCollection->addDataSet(new DataSet((string) $dataSetName, $dataSetData));
            }
        }

        return $dataSetCollection;
    }
}
