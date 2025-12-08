<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit\Validator\Assertion;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\BasilLoader\Tests\Unit\Validator\ValueDataProviderTrait;
use webignition\BasilLoader\Validator\Assertion\AssertionContentValidator;
use webignition\BasilLoader\Validator\InvalidResult;
use webignition\BasilLoader\Validator\ResultType;
use webignition\BasilLoader\Validator\ValidResult;

class AssertionContentValidatorTest extends TestCase
{
    use ValueDataProviderTrait;

    private AssertionContentValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = AssertionContentValidator::create();
    }

    #[DataProvider('invalidValueDataProvider')]
    public function testValidateNotValid(string $value, string $expectedReason): void
    {
        $expectedResult = new InvalidResult($value, ResultType::VALUE, $expectedReason);

        $this->assertEquals($expectedResult, $this->validator->validate($value));
    }

    #[DataProvider('validValueDataProvider')]
    #[DataProvider('validAssertionValueDataProvider')]
    public function testValidateIsValid(string $value): void
    {
        $expectedResult = new ValidResult($value);

        $this->assertEquals($expectedResult, $this->validator->validate($value));
    }

    /**
     * @return array<mixed>
     */
    public static function validAssertionValueDataProvider(): array
    {
        return [
            'descendant element dom identifier' => [
                'value' => '$"parent" >> $".selector"',
            ],
            'descendant attribute dom identifier' => [
                'value' => '$"parent" >> $".selector".attribute_name',
            ],
        ];
    }
}
