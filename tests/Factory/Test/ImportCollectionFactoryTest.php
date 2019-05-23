<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Factory\Test;

use webignition\BasilParser\Factory\Test\ImportCollectionFactory;
use webignition\BasilParser\Model\Test\ImportCollection;
use webignition\BasilParser\Model\Test\ImportCollectionInterface;

class ImportCollectionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ImportCollectionFactory
     */
    private $importCollectionFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCollectionFactory = new ImportCollectionFactory();
    }

    /**
     * @dataProvider createFromImportCollectionDataDataProvider
     */
    public function testCreateFromImportCollectionData(
        array $importCollectionData,
        ImportCollectionInterface $expectedImportCollection
    ) {
        $importCollection = $this->importCollectionFactory->createFromImportCollectionData($importCollectionData);

        $this->assertEquals($expectedImportCollection, $importCollection);
    }

    public function createFromImportCollectionDataDataProvider(): array
    {
        return [
            'empty' => [
                'importCollectionData' => [],
                'expectedImportCollection' => new ImportCollection([], []),
            ],
            'non-array collections' => [
                'importCollectionData' => [
                    ImportCollectionFactory::KEY_PAGES => 1,
                    ImportCollectionFactory::KEY_STEPS => false,
                ],
                'expectedImportCollection' => new ImportCollection([], []),
            ],
            'non-string import paths' => [
                'importCollectionData' => [
                    ImportCollectionFactory::KEY_PAGES => [
                        'foo' => false,
                    ],
                    ImportCollectionFactory::KEY_STEPS => [
                        'bar' => 1,
                    ],
                ],
                'expectedImportCollection' => new ImportCollection([], []),
            ],
            'string import paths' => [
                'importCollectionData' => [
                    ImportCollectionFactory::KEY_PAGES => [
                        'page_one' => '../page/one.yml',
                        'page_two' => '../page/two.yml',
                    ],
                    ImportCollectionFactory::KEY_STEPS => [
                        'step_one' => '../step/one.yml',
                    ],
                ],
                'expectedImportCollection' => new ImportCollection(
                    [
                        'page_one' => '../page/one.yml',
                        'page_two' => '../page/two.yml',
                    ],
                    [
                        'step_one' => '../step/one.yml',
                    ]
                ),
            ],
        ];
    }
}
