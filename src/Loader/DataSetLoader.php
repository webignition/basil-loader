<?php

namespace webignition\BasilParser\Loader;

use webignition\BasilModel\DataSet\DataSet;
use webignition\BasilModel\DataSet\DataSetCollection;
use webignition\BasilModel\DataSet\DataSetCollectionInterface;
use webignition\BasilParser\Exception\YamlLoaderException;

class DataSetLoader
{
    private $yamlLoader;

    public function __construct(YamlLoader $yamlLoader)
    {
        $this->yamlLoader = $yamlLoader;
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
