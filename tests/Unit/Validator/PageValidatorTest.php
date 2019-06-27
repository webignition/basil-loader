<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Validator;

use Nyholm\Psr7\Uri;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Page\PageInterface;
use webignition\BasilParser\Validator\PageValidator;

class PageValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PageValidator
     */
    private $pageValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pageValidator = new PageValidator();
    }

    /**
     * @dataProvider validateDataProvider
     */
    public function testValidate(PageInterface $page, bool $expectedIsValid)
    {
        $this->assertSame($expectedIsValid, $this->pageValidator->validate($page));
    }

    public function validateDataProvider(): array
    {
        return [
            'empty uri is not valid' => [
                'page' => new Page(new Uri(''), []),
                'expectedIsValid' => false,
            ],
            'non-empty uri is valid' => [
                'page' => new Page(new Uri('http://example.com/'), []),
                'expectedIsValid' => true,
            ],
        ];
    }
}
