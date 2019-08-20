<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Provider\DataSet;

use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Provider\DataSet\DataSetProvider;

class DataSetProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testFindPageThrowsUnknownDataProviderException()
    {
        $this->expectException(UnknownDataProviderException::class);
        $this->expectExceptionMessage('Unknown data provider "data_provider_import_name"');

        $dataSetProvider = new DataSetProvider([]);
        $dataSetProvider->findDataSetCollection('data_provider_import_name');
    }
}
