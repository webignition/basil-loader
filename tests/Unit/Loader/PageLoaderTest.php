<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Loader;

use Nyholm\Psr7\Uri;
use webignition\BasilParser\Loader\PageLoader;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;
use webignition\BasilParser\Model\Page\Page;
use webignition\BasilParser\Model\Page\PageInterface;
use webignition\BasilParser\Tests\Services\FixturePathFinder;
use webignition\BasilParser\Tests\Services\PageFactoryFactory;
use webignition\BasilParser\Tests\Services\YamlLoaderFactory;

class PageLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider loadDataProvider
     */
    public function testLoad(string $path, PageInterface $expectedPage)
    {
        $pageLoader = new PageLoader(YamlLoaderFactory::create(), PageFactoryFactory::create());

        $page = $pageLoader->load($path);

        $this->assertEquals($expectedPage, $page);
    }

    public function loadDataProvider(): array
    {
        $parentIdentifier = new Identifier(
            IdentifierTypes::CSS_SELECTOR,
            '.form',
            null,
            'form'
        );

        return [
            'empty' => [
                'path' => FixturePathFinder::find('Empty/empty.yml'),
                'expectedPage' => new Page(new Uri(''), []),
            ],
            'url only' => [
                'path' => FixturePathFinder::find('Page/example.com.url-only.yml'),
                'expectedPage' => new Page(new Uri('https://example.com'), []),
            ],
            'url and element references' => [
                'path' => FixturePathFinder::find('Page/example.com.form.yml'),
                'expectedPage' => new Page(
                    new Uri('https://example.com'),
                    [
                        'form' => $parentIdentifier,
                        'input' =>
                            (new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.input',
                                null,
                                'input'
                            ))->withParentIdentifier($parentIdentifier),
                    ]
                ),
            ],
        ];
    }
}
