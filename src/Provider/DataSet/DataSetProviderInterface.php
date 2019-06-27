<?php

namespace webignition\BasilParser\Provider\DataSet;

use webignition\BasilModel\DataSet\DataSetInterface;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\UnknownDataProviderException;

interface DataSetProviderInterface
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
