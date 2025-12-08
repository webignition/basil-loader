<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit\Validator;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\BasilLoader\Validator\InvalidResult;
use webignition\BasilLoader\Validator\ResultType;
use webignition\BasilLoader\Validator\ValidResult;
use webignition\BasilLoader\Validator\ValueValidator;

class ValueValidatorTest extends TestCase
{
    use ValueDataProviderTrait;

    private ValueValidator $valueValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->valueValidator = ValueValidator::create();
    }

    #[DataProvider('invalidValueDataProvider')]
    public function testValidateNotValid(string $value, string $expectedReason): void
    {
        $expectedResult = new InvalidResult($value, ResultType::VALUE, $expectedReason);

        $this->assertEquals($expectedResult, $this->valueValidator->validate($value));
    }

    #[DataProvider('validValueDataProvider')]
    public function testValidateIsValid(string $value): void
    {
        $expectedResult = new ValidResult($value);

        $this->assertEquals($expectedResult, $this->valueValidator->validate($value));
    }
}
