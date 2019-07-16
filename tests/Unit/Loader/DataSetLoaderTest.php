<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Loader;

use webignition\BasilModel\DataSet\DataSet;
use webignition\BasilModel\DataSet\DataSetCollection;
use webignition\BasilModel\DataSet\DataSetCollectionInterface;
use webignition\BasilParser\Tests\Services\DataSetLoaderFactory;
use webignition\BasilParser\Tests\Services\FixturePathFinder;

class DataSetLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider loadDataProvider
     */
    public function testLoad(string $path, DataSetCollectionInterface $expectedDataSetCollection)
    {
        $dataSetLoader = DataSetLoaderFactory::create();

        $dataSetCollection = $dataSetLoader->load($path);

        $this->assertEquals($expectedDataSetCollection, $dataSetCollection);
    }

    public function loadDataProvider(): array
    {
        return [
            'empty' => [
                'path' => FixturePathFinder::find('Empty/empty.yml'),
                'expectedPage' => new DataSetCollection(),
            ],
            'non-empty, expected title only' => [
                'path' => FixturePathFinder::find('DataProvider/expected-title-only.yml'),
                'expectedPage' => new DataSetCollection([
                    new DataSet([
                        'expected_title' => 'Foo',
                    ]),
                    new DataSet([
                        'expected_title' => 'Bar',
                    ]),
                ]),
            ],
            'non-empty, users' => [
                'path' => FixturePathFinder::find('DataProvider/users.yml'),
                'expectedPage' => new DataSetCollection([
                    'user1' => new DataSet([
                        'username' => 'user1',
                        'role' => 'user',
                    ]),
                    'user2' => new DataSet([
                        'username' => 'user2',
                        'role' => 'admin',
                    ]),
                ]),
            ],
        ];
    }
}
