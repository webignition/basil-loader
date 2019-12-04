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
    public function testLoad(string $path, PageInterface $expectedPage)
    {
        $pageLoader = PageLoader::createLoader();

        $page = $pageLoader->load($path);

        $this->assertEquals($expectedPage, $page);
    }

    public function loadDataProvider(): array
    {
        return [
            'empty' => [
                'path' => FixturePathFinder::find('Empty/empty.yml'),
                'expectedPage' => new Page(''),
            ],
            'url only' => [
                'path' => FixturePathFinder::find('Page/example.com.url-only.yml'),
                'expectedPage' => new Page('https://example.com'),
            ],
            'url and element references' => [
                'path' => FixturePathFinder::find('Page/example.com.form.yml'),
                'expectedPage' => new Page(
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
