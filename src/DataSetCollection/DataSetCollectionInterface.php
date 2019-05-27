<?php

namespace webignition\BasilParser\DataSetCollection;

use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Model\DataSet\DataSetInterface;

interface DataSetCollectionInterface
{
    /**
     * @param string $importName
     *
     * @return DataSetInterface[]
     *
     * @throws NonRetrievableDataProviderException
     * @throws UnknownDataProviderException
     */
    public function findDataSetCollection(string $importName): array;
}
