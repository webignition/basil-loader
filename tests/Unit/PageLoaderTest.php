<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit;

use Nyholm\Psr7\Uri;
use webignition\BasilLoader\PageLoader;
use webignition\BasilLoader\Tests\Services\FixturePathFinder;
use webignition\BasilModel\Identifier\DomIdentifierCollection;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Page\PageInterface;
use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;

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
        $parentIdentifier = TestIdentifierFactory::createElementIdentifier('.form', null, 'form');

        return [
            'empty' => [
                'path' => FixturePathFinder::find('Empty/empty.yml'),
                'expectedPage' => new Page(new Uri(''), new DomIdentifierCollection()),
            ],
            'url only' => [
                'path' => FixturePathFinder::find('Page/example.com.url-only.yml'),
                'expectedPage' => new Page(new Uri('https://example.com'), new DomIdentifierCollection()),
            ],
            'url and element references' => [
                'path' => FixturePathFinder::find('Page/example.com.form.yml'),
                'expectedPage' => new Page(
                    new Uri('https://example.com'),
                    new DomIdentifierCollection([
                        'form' => $parentIdentifier,
                        'input' => TestIdentifierFactory::createElementIdentifier(
                            '.input',
                            null,
                            'input',
                            $parentIdentifier
                        ),
                    ])
                ),
            ],
        ];
    }
}
