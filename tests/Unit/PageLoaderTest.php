<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit;

use webignition\BasilLoader\PageLoader;
use webignition\BasilLoader\Tests\Services\FixturePathFinder;
use webignition\BasilModels\Page\Page;
use webignition\BasilModels\Page\PageInterface;

class PageLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider loadDataProvider
     */
    public function testLoad(string $importName, string $path, PageInterface $expectedPage)
    {
        $pageLoader = PageLoader::createLoader();

        $page = $pageLoader->load($importName, $path);

        $this->assertEquals($expectedPage, $page);
    }

    public function loadDataProvider(): array
    {
        return [
            'empty' => [
                'importName' => 'import_name',
                'path' => FixturePathFinder::find('Empty/empty.yml'),
                'expectedPage' => new Page('import_name', ''),
            ],
            'url only' => [
                'importName' => 'import_name',
                'path' => FixturePathFinder::find('Page/example.com.url-only.yml'),
                'expectedPage' => new Page('import_name', 'https://example.com'),
            ],
            'url and element references' => [
                'importName' => 'import_name',
                'path' => FixturePathFinder::find('Page/example.com.form.yml'),
                'expectedPage' => new Page(
                    'import_name',
                    'https://example.com',
                    [
                        'form' => '$".form"',
                        'input' => '$"{{ form }} .input"',
                    ]
                ),
            ],
        ];
    }
}
