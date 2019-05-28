<?php

namespace webignition\BasilParser\Provider\DataSet;

use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Model\DataSet\DataSetInterface;

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
