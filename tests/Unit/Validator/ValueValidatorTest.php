<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit\Validator;

use webignition\BasilDataValidator\ResultType;
use webignition\BasilDataValidator\ValueValidator;
use webignition\BasilValidationResult\InvalidResult;
use webignition\BasilValidationResult\ValidResult;

class ValueValidatorTest extends \PHPUnit\Framework\TestCase
{
    use ValueDataProviderTrait;

    private ValueValidator $valueValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->valueValidator = ValueValidator::create();
    }

    /**
     * @dataProvider invalidValueDataProvider
     */
    public function testValidateNotValid(string $value, string $expectedReason): void
    {
        $expectedResult = new InvalidResult($value, ResultType::VALUE, $expectedReason);

        $this->assertEquals($expectedResult, $this->valueValidator->validate($value));
    }

    /**
     * @dataProvider validValueDataProvider
     */
    public function testValidateIsValid(string $value): void
    {
        $expectedResult = new ValidResult($value);

        $this->assertEquals($expectedResult, $this->valueValidator->validate($value));
    }
}
