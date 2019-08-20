<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Services\Provider;

use webignition\BasilModel\DataSet\DataSetCollectionInterface;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Provider\DataSet\DataSetProviderInterface;

class EmptyDataSetProvider implements DataSetProviderInterface
{
    /**
     * @param string $importName
     *
     * @return DataSetCollectionInterface
     *
     * @throws UnknownDataProviderException
     */
    public function findDataSetCollection(string $importName): DataSetCollectionInterface
    {
        throw new UnknownDataProviderException($importName);
    }
}
