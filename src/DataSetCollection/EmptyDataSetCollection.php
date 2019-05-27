<?php

namespace webignition\BasilParser\DataSetCollection;

use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Model\DataSet\DataSetInterface;

class EmptyDataSetCollection implements DataSetCollectionInterface
{
    /**
     * @param string $importName
     *
     * @return DataSetInterface[]
     *
     * @throws UnknownDataProviderException
     */
    public function findDataSetCollection(string $importName): array
    {
        throw new UnknownDataProviderException($importName);
    }
}
