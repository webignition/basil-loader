<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\DataStructure;

use webignition\BasilParser\DataStructure\ImportList;

class ImportListTest extends \PHPUnit\Framework\TestCase
{
//    public function testEmptyList()
//    {
//        $importList = new ImportList([], '');
//
//        $this->assertSame([], $importList->getPaths());
//    }

    /**
     * @dataProvider pathsDataProvider
     */
    public function testGetPaths($paths, string $basePath, array $expectedPaths)
    {
        $importList = new ImportList($paths, $basePath);

        $this->assertSame($expectedPaths, $importList->getPaths());
    }

    public function pathsDataProvider(): array
    {
        return [
            'empty' => [
                'paths' => [],
                'basePath' => '',
                'expectedPaths' => [],
            ],
            'relative import path, no base path' => [
                'paths' => [
                    'foo' => '../Relative/foo.yml',
                ],
                'basePath' => '',
                'expectedPaths' => [
                    'foo' => '../Relative/foo.yml',
                ],
            ],
            'relative import path, has base path; previous directory' => [
                'paths' => [
                    'foo' => '../Relative/foo.yml',
                ],
                'basePath' => '/basil/Test/',
                'expectedPaths' => [
                    'foo' => '/basil/Relative/foo.yml',
                ],
            ],
            'relative import path, has base path; current directory' => [
                'paths' => [
                    'foo' => './Relative/foo.yml',
                ],
                'basePath' => '/basil/Test/',
                'expectedPaths' => [
                    'foo' => '/basil/Test/Relative/foo.yml',
                ],
            ],
            'absolute import path, no base path' => [
                'paths' => [
                    'foo' => './Relative/foo.yml',
                ],
                'basePath' => '/basil/Test/',
                'expectedPaths' => [
                    'foo' => '/basil/Test/Relative/foo.yml',
                ],
            ],
            'integer' => [
                'paths' => [
                    'foo' => 123,
                ],
                'basePath' => '/basil/Test/',
                'expectedPaths' => [
                    'foo' => '123',
                ],
            ],
        ];
    }
}
