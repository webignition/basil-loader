<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Loader;

use Nyholm\Psr7\Uri;
use webignition\BasilParser\Loader\PageLoader;
use webignition\BasilParser\Loader\YamlLoader;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;
use webignition\BasilParser\Model\Page\Page;
use webignition\BasilParser\Model\Page\PageInterface;
use webignition\BasilParser\Tests\Services\PageFactoryFactory;

class PageLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider loadDataProvider
     */
    public function testLoad(array $yamlLoaderReturnValue, PageInterface $expectedPage)
    {
        $path = 'page.yml';

        $yamlLoader = \Mockery::mock(YamlLoader::class);
        $yamlLoader
            ->shouldReceive('loadArray')
            ->with($path)
            ->andReturn($yamlLoaderReturnValue);

        $pageFactory = PageFactoryFactory::create();

        $pageLoader = new PageLoader($yamlLoader, $pageFactory);

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
                'yamlLoaderReturnValue' => [],
                'expectedPage' => new Page(new Uri(''), []),
            ],
            'url only' => [
                'yamlLoaderReturnValue' => [
                    'url' => 'http://example.com',
                ],
                'expectedPage' => new Page(new Uri('http://example.com'), []),
            ],
            'url and element references' => [
                'yamlLoaderReturnValue' => [
                    'url' => 'http://example.com',
                    'elements' => [
                        'form' => '".form"',
                        'input' => '"{{ form}} .input"',
                    ],
                ],
                'expectedPage' => new Page(
                    new Uri('http://example.com'),
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

    protected function tearDown(): void
    {
        parent::tearDown();

        \Mockery::close();
    }
}
