<?php

namespace webignition\BasilParser\Provider\DataSet;

use webignition\BasilModel\DataSet\DataSetInterface;
use webignition\BasilParser\Exception\UnknownDataProviderException;

class EmptyDataSetProvider implements DataSetProviderInterface
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
