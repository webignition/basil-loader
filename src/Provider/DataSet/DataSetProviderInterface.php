<?php

namespace webignition\BasilParser\Provider\DataSet;

use webignition\BasilModel\DataSet\DataSetCollectionInterface;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\UnknownDataProviderException;

interface DataSetProviderInterface
{
    /**
     * @param string $importName
     *
     * @return DataSetCollectionInterface
     *
     * @throws NonRetrievableDataProviderException
     * @throws UnknownDataProviderException
     */
    public function findDataSetCollection(string $importName): DataSetCollectionInterface;
}
