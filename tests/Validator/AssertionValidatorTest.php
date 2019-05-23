<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Validator;

use webignition\BasilParser\Factory\AssertionFactory;
use webignition\BasilParser\Validator\AssertionValidator;

class AssertionValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AssertionValidator
     */
    private $assertionValidator;

    /**
     * @var AssertionFactory
     */
    private $assertionFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertionValidator = new AssertionValidator();
        $this->assertionFactory = new AssertionFactory();
    }

    /**
     * @dataProvider validateDataProvider
     */
    public function testValidate(string $assertionString, bool $expectedIsValid)
    {
        $assertion = $this->assertionFactory->createFromAssertionString($assertionString);

        $this->assertSame($expectedIsValid, $this->assertionValidator->validate($assertion));
    }

    public function validateDataProvider(): array
    {
        return [
            'empty assertion string' => [
                'assertionString' => '',
                'expectedIsValid' => false,
            ],
            'simple css selector, is, scalar value' => [
                'assertionString' => '".selector" is "value"',
                'expectedIsValid' => true,
            ],
            'simple css selector, is, data parameter value' => [
                'assertionString' => '".selector" is $data.name',
                'expectedIsValid' => true,
            ],
            'simple css selector, is, element parameter value' => [
                'actionString' => '".selector" is $elements.name',
                'expectedIsValid' => true,
            ],
            'simple css selector, is, escaped quotes scalar value' => [
                'assertionString' => '".selector" is "\"value\""',
                'expectedIsValid' => true,
            ],
            'simple css selector, is, lacking value' => [
                'assertionString' => '".selector" is',
                'expectedIsValid' => false,
            ],
            'simple css selector, is-not, scalar value' => [
                'assertionString' => '".selector" is-not "value"',
                'expectedIsValid' => true,
            ],
            'simple css selector, is-not, lacking value' => [
                'assertionString' => '".selector" is-not',
                'expectedIsValid' => false,
            ],
            'simple css selector, exists, no value' => [
                'assertionString' => '".selector" exists',
                'expectedIsValid' => true,
            ],
            'simple css selector, exists, scalar value is ignored' => [
                'assertionString' => '".selector" exists "value"',
                'expectedIsValid' => true,
            ],
            'simple css selector, exists, data parameter value is ignored' => [
                'assertionString' => '".selector" exists $data.name"',
                'expectedIsValid' => true,
            ],
            'simple css selector, includes, scalar value' => [
                'assertionString' => '".selector" includes "value"',
                'expectedIsValid' => true,
            ],
            'simple css selector, includes, lacking value' => [
                'assertionString' => '".selector" includes',
                'expectedIsValid' => false,
            ],
            'simple css selector, excludes, scalar value' => [
                'assertionString' => '".selector" excludes "value"',
                'expectedIsValid' => true,
            ],
            'simple css selector, excludes, lacking value' => [
                'assertionString' => '".selector" excludes',
                'expectedIsValid' => false,
            ],
            'simple css selector, matches, scalar value' => [
                'assertionString' => '".selector" matches "value"',
                'expectedIsValid' => true,
            ],
            'simple css selector, matches, lacking value' => [
                'assertionString' => '".selector" matches',
                'expectedIsValid' => false,
            ],
            'comparison-including css selector, is, scalar value' => [
                'assertionString' => '".selector is is-not exists not-exists includes excludes matches foo" is "value"',
                'expectedIsValid' => true,
            ],
            'simple xpath expression, is, scalar value' => [
                'assertionString' => '"//foo" is "value"',
                'expectedIsValid' => true,
            ],
            'comparison-including non-simple xpath expression, is, scalar value' => [
                'assertionString' =>
                    '"//a[ends-with(@href is exists not-exists matches includes excludes, ".pdf")]" is "value"',
                'expectedIsValid' => true,
            ],
        ];
    }
}
