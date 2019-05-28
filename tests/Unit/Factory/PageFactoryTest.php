<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Factory;

use Nyholm\Psr7\Uri;
use webignition\BasilParser\Factory\PageFactory;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;
use webignition\BasilParser\Model\Page\Page;
use webignition\BasilParser\Model\Page\PageInterface;
use webignition\BasilParser\Tests\Services\PageFactoryFactory;

class PageFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pageFactory = PageFactoryFactory::create();
    }

    /**
     * @dataProvider createFromPageDataDataProvider
     */
    public function testCreateFromPageData(array $pageData, PageInterface $expectedPage)
    {
        $page = $this->pageFactory->createFromPageData($pageData);

        $this->assertInstanceOf(PageInterface::class, $page);
        $this->assertEquals($expectedPage, $page);
    }

    public function createFromPageDataDataProvider(): array
    {
        $parentIdentifier = new Identifier(
            IdentifierTypes::CSS_SELECTOR,
            '.form',
            null,
            'form'
        );

        return [
            'empty page data' => [
                'pageData' => [],
                'expectedPage' => new Page(new Uri(''), []),
            ],
            'has url, empty elements data' => [
                'pageData' => [
                    PageFactory::KEY_URL => new Uri('http://example.com/'),
                ],
                'expectedPage' => new Page(new Uri('http://example.com/'), []),
            ],
            'elements is not an array' => [
                'pageData' => [
                    PageFactory::KEY_URL => new Uri('http://example.com/'),
                    PageFactory::KEY_ELEMENTS => true,
                ],
                'expectedPage' => new Page(new Uri('http://example.com/'), [])
            ],
            'single element identifier' => [
                'pageData' => [
                    PageFactory::KEY_URL => new Uri('http://example.com/'),
                    PageFactory::KEY_ELEMENTS => [
                        'css-selector' => '".selector"',
                    ],
                ],
                'expectedPage' => new Page(
                    new Uri('http://example.com/'),
                    [
                        'css-selector' => new Identifier(
                            IdentifierTypes::CSS_SELECTOR,
                            '.selector',
                            null,
                            'css-selector'
                        ),
                    ]
                ),
            ],
            'referenced element identifier' => [
                'pageData' => [
                    PageFactory::KEY_URL => new Uri('http://example.com/'),
                    PageFactory::KEY_ELEMENTS => [
                        'form' => '".form"',
                        'form_field' => '"{{ form }} .field"',
                    ],
                ],
                'expectedPage' => new Page(
                    new Uri('http://example.com/'),
                    [
                        'form' => $parentIdentifier,
                        'form_field' =>
                            (new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.field',
                                null,
                                'form_field'
                            ))->withParentIdentifier($parentIdentifier),
                    ]
                ),
            ],
        ];
    }
}
