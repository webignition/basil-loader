<?php

namespace webignition\BasilParser\Provider\DataSet;

use webignition\BasilModel\DataSet\DataSetCollectionInterface;
use webignition\BasilParser\Exception\UnknownDataProviderException;

class PopulatedDataSetProvider implements DataSetProviderInterface
{
    private $dataSetCollections = [];

    public function __construct(array $dataSetCollections)
    {
        foreach ($dataSetCollections as $importName => $dataSetCollection) {
            if (is_array($dataSetCollection)) {
                $this->dataSetCollections[$importName] = $dataSetCollection;
            }
        }
    }

    /**
     * @param string $importName
     *
     * @return DataSetCollectionInterface
     *
     * @throws UnknownDataProviderException
     */
    public function findDataSetCollection(string $importName): DataSetCollectionInterface
    {
        $dataSetCollection = $this->dataSetCollections[$importName] ?? null;

        if (null === $dataSetCollection) {
            throw new UnknownDataProviderException($importName);
        }

        return $dataSetCollection;
    }
}
