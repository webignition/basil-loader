<?php

namespace webignition\BasilParser\Loader;

use webignition\BasilParser\Model\DataSet\DataSet;

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
     * @return DataSet[]
     *
     * @throws YamlLoaderException
     */
    public function load(string $path): array
    {
        $data = $this->yamlLoader->loadArray($path);

        $dataSets = [];

        foreach ($data as $dataSetData) {
            if (is_array($dataSetData)) {
                $dataSets[] = new DataSet($dataSetData);
            }
        }

        return $dataSets;
    }
}
