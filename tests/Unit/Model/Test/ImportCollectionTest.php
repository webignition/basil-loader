<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Model\Test;

use webignition\BasilParser\Model\Test\ImportCollection;
use webignition\BasilParser\Model\Test\ImportCollectionInterface;

class ImportCollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(array $importPaths, ImportCollectionInterface $expectedImportCollection)
    {
        $importCollection = new ImportCollection($importPaths);

        $this->assertEquals($expectedImportCollection, $importCollection);
    }

    public function createDataProvider(): array
    {
        return [
            'no imports' => [
                'importPaths' => [],
                'expectedImportCollection' => new ImportCollection([]),
            ],
            'invalid imports' => [
                'importPaths' => [
                    1,
                    2,
                    true,
                    false,
                ],
                'expectedImportCollection' => new ImportCollection([]),
            ],
            'valid and invalid imports' => [
                'importPaths' => [
                    'invalid1' => 1,
                    'invalid2' => 2,
                    'page' => '../page/example.com.yml',
                ],
                'expectedImportCollection' => new ImportCollection(
                    [
                        'page' => '../page/example.com.yml',
                    ]
                ),
            ],
        ];
    }
}
