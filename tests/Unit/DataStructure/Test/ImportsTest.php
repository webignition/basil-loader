<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\DataStructure\Test;

use webignition\BasilParser\DataStructure\Test\Imports;

class ImportsTest extends \PHPUnit\Framework\TestCase
{
    public function testEmptyImports()
    {
        $importsDataStructure = new Imports([]);

        $this->assertSame([], $importsDataStructure->getStepPaths());
        $this->assertSame([], $importsDataStructure->getPagePaths());
        $this->assertSame([], $importsDataStructure->getDataProviderPaths());
    }

    /**
     * @dataProvider pathsDataProvider
     */
    public function testGetStepPaths($paths, string $basePath, array $expectedPaths)
    {
        $importsDataStructure = new Imports([
            Imports::KEY_STEPS => $paths,
        ]);

        $this->assertSame($expectedPaths, $importsDataStructure->getStepPaths($basePath));
    }

    /**
     * @dataProvider pathsDataProvider
     */
    public function testGetPagePaths($paths, string $basePath, array $expectedPaths)
    {
        $importsDataStructure = new Imports([
            Imports::KEY_PAGES => $paths,
        ]);

        $this->assertSame($expectedPaths, $importsDataStructure->getPagePaths($basePath));
    }

    /**
     * @dataProvider pathsDataProvider
     */
    public function testGetDataProviderPaths($paths, string $basePath, array $expectedPaths)
    {
        $importsDataStructure = new Imports([
            Imports::KEY_DATA_PROVIDERS => $paths,
        ]);

        $this->assertSame($expectedPaths, $importsDataStructure->getDataProviderPaths($basePath));
    }

    public function pathsDataProvider(): array
    {
        return [
            'not an array' => [
                'paths' => 'not an array',
                'basePath' => '',
                'expectedPaths' => [],
            ],
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
        ];
    }
}
