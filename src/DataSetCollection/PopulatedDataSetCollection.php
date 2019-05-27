<?php

namespace webignition\BasilParser\DataSetCollection;

use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Model\DataSet\DataSetInterface;

class PopulatedDataSetCollection implements DataSetCollectionInterface
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
     * @return DataSetInterface[]
     *
     * @throws UnknownDataProviderException
     */
    public function findDataSetCollection(string $importName): array
    {
        $dataSetCollection = $this->dataSetCollections[$importName] ?? null;

        if (null === $dataSetCollection) {
            throw new UnknownDataProviderException($importName);
        }

        return $dataSetCollection;
    }
}
