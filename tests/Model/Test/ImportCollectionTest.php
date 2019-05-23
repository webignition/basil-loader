<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Model\Test;

use webignition\BasilParser\Model\Test\ImportCollection;
use webignition\BasilParser\Model\Test\ImportCollectionInterface;

class ImportCollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(
        array $pageImportPaths,
        array $stepImportPaths,
        ImportCollectionInterface $expectedImportCollection
    ) {
        $importCollection = new ImportCollection($pageImportPaths, $stepImportPaths);

        $this->assertEquals($expectedImportCollection, $importCollection);
    }

    public function createDataProvider(): array
    {
        return [
            'no page imports, no step imports' => [
                'pageImportPaths' => [],
                'stepImportPaths' => [],
                'expectedImportCollection' => new ImportCollection([], []),
            ],
            'invalid page imports, invalid step imports' => [
                'pageImportPaths' => [
                    1,
                    2,
                ],
                'stepImportPaths' => [
                    true,
                    false,
                ],
                'expectedImportCollection' => new ImportCollection([], []),
            ],
            'valid and invalid page imports, valid and invalid step imports' => [
                'pageImportPaths' => [
                    'invalid1' => 1,
                    'invalid2' => 2,
                    'page' => '../page/example.com.yml',
                ],
                'stepImportPaths' => [
                    'invalid1' => true,
                    'invalid2' => false,
                    'step' => '../step/verify.yml',
                ],
                'expectedImportCollection' => new ImportCollection(
                    [
                        'page' => '../page/example.com.yml',
                    ],
                    [
                        'step' => '../step/verify.yml',
                    ]
                ),
            ],
        ];
    }
}
