<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Loader;

use Nyholm\Psr7\Uri;
use webignition\BasilParser\Factory\PageFactory;
use webignition\BasilParser\Loader\PageLoader;
use webignition\BasilParser\Loader\YamlLoader;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;
use webignition\BasilParser\Model\Page\Page;
use webignition\BasilParser\Model\Page\PageInterface;

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

        $pageLoader = new PageLoader($yamlLoader, new PageFactory());

        $page = $pageLoader->load($path);

        $this->assertEquals($expectedPage, $page);
    }

    public function loadDataProvider(): array
    {
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
                        'form' => new Identifier(
                            IdentifierTypes::CSS_SELECTOR,
                            '.form'
                        ),
                        'input' =>
                            (new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.input'
                            ))->withElementReference('form'),
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
