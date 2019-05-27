<?php

namespace webignition\BasilParser\DataSetProvider;

use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Model\DataSet\DataSetInterface;

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
