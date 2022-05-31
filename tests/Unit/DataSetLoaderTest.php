<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit;

use webignition\BasilLoader\DataSetLoader;
use webignition\BasilLoader\Tests\Services\FixturePathFinder;
use webignition\BasilModels\Model\DataSet\DataSetCollection;
use webignition\BasilModels\Model\DataSet\DataSetCollectionInterface;

class DataSetLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider loadDataProvider
     */
    public function testLoad(string $path, DataSetCollectionInterface $expectedDataSetCollection): void
    {
        $dataSetLoader = DataSetLoader::createLoader();

        $dataSetCollection = $dataSetLoader->load($path);

        $this->assertEquals($expectedDataSetCollection, $dataSetCollection);
    }

    /**
     * @return array<mixed>
     */
    public function loadDataProvider(): array
    {
        return [
            'empty' => [
                'path' => FixturePathFinder::find('Empty/empty.yml'),
                'expectedPage' => new DataSetCollection([]),
            ],
            'non-empty, expected title only' => [
                'path' => FixturePathFinder::find('DataProvider/expected-title-only.yml'),
                'expectedPage' => new DataSetCollection([
                    '0' => [
                        'expected_title' => 'Foo',
                    ],
                    '1' => [
                        'expected_title' => 'Bar',
                    ],
                ]),
            ],
            'non-empty, users' => [
                'path' => FixturePathFinder::find('DataProvider/users.yml'),
                'expectedPage' => new DataSetCollection([
                    'user1' => [
                        'username' => 'user1',
                        'role' => 'user',
                    ],
                    'user2' => [
                        'username' => 'user2',
                        'role' => 'admin',
                    ],
                ]),
            ],
        ];
    }
}
