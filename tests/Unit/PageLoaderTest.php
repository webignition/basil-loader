<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilLoader\Tests\Unit;

use Nyholm\Psr7\Uri;
use webignition\BasilLoader\PageLoader;
use webignition\BasilModel\Identifier\IdentifierCollection;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Page\PageInterface;
use webignition\BasilLoader\Tests\Services\FixturePathFinder;
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
        $parentIdentifier = TestIdentifierFactory::createCssElementIdentifier('.form', 1, 'form');

        return [
            'empty' => [
                'path' => FixturePathFinder::find('Empty/empty.yml'),
                'expectedPage' => new Page(new Uri(''), new IdentifierCollection()),
            ],
            'url only' => [
                'path' => FixturePathFinder::find('Page/example.com.url-only.yml'),
                'expectedPage' => new Page(new Uri('https://example.com'), new IdentifierCollection()),
            ],
            'url and element references' => [
                'path' => FixturePathFinder::find('Page/example.com.form.yml'),
                'expectedPage' => new Page(
                    new Uri('https://example.com'),
                    new IdentifierCollection([
                        'form' => $parentIdentifier,
                        'input' => TestIdentifierFactory::createCssElementIdentifier(
                            '.input',
                            1,
                            'input',
                            $parentIdentifier
                        ),
                    ])
                ),
            ],
        ];
    }
}