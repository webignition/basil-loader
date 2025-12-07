<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit\Validator\Test;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use webignition\BasilLoader\Validator\InvalidResult;
use webignition\BasilLoader\Validator\InvalidResultInterface;
use webignition\BasilLoader\Validator\ResultType;
use webignition\BasilLoader\Validator\Step\StepValidator;
use webignition\BasilLoader\Validator\Test\TestValidator;
use webignition\BasilLoader\Validator\ValidResult;
use webignition\BasilModels\Model\Assertion\Assertion;
use webignition\BasilModels\Model\Step\Step;
use webignition\BasilModels\Model\Step\StepCollection;
use webignition\BasilModels\Model\Test\Test;
use webignition\BasilModels\Model\Test\TestInterface;
use webignition\BasilModels\Parser\Test\TestParser;

class TestValidatorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private TestValidator $testValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testValidator = TestValidator::create();
    }

    /**
     * @dataProvider validateNotValidDataProvider
     */
    public function testValidateNotValid(TestInterface $test, InvalidResultInterface $expectedResult): void
    {
        $this->assertEquals($expectedResult, $this->testValidator->validate($test));
    }

    /**
     * @return array<mixed>
     */
    public static function validateNotValidDataProvider(): array
    {
        $testWithNoSteps = new Test('chrome', 'http://example.com/', new StepCollection([]));

        $invalidStep = new Step([], []);
        $testWithInvalidStep = new Test('chrome', 'http://example.com/', new StepCollection([
            'invalid step name' => $invalidStep,
        ]));

        $validStep = new Step([], [
            new Assertion('$page.title is "Example"', '$page.title', 'is', '"Example"'),
        ]);
        $validStepCollection = new StepCollection(['step name' => $validStep]);

        return [
            'invalid configuration: url is page reference' => [
                'test' => new Test('chrome', '$page_import_name.url', $validStepCollection),
                'expectedResult' => new InvalidResult(
                    new Test('chrome', '$page_import_name.url', $validStepCollection),
                    ResultType::TEST,
                    TestValidator::REASON_URL_IS_PAGE_URL_REFERENCE
                ),
            ],
            'no steps' => [
                'test' => $testWithNoSteps,
                'expectedResult' => new InvalidResult(
                    $testWithNoSteps,
                    ResultType::TEST,
                    TestValidator::REASON_NO_STEPS
                ),
            ],
            'invalid step' => [
                'test' => $testWithInvalidStep,
                'expectedResult' => (new InvalidResult(
                    $testWithInvalidStep,
                    ResultType::TEST,
                    TestValidator::REASON_STEP_INVALID,
                    new InvalidResult(
                        $invalidStep,
                        ResultType::STEP,
                        StepValidator::REASON_NO_ASSERTIONS
                    )
                ))->withContext([
                    TestValidator::CONTEXT_STEP_NAME => 'invalid step name',
                ]),
            ],
        ];
    }

    public function testValidateIsValid(): void
    {
        $testParser = TestParser::create();

        $test = $testParser->parse([
            'config' => [
                'browser' => 'chrome',
                'url' => 'http://example.com',
            ],
            'step name' => [
                'actions' => [
                    'click $".selector"',
                ],
                'assertions' => [
                    '$page.title is "Example"',
                ],
            ],
        ]);

        $expectedResult = new ValidResult($test);

        $this->assertEquals($expectedResult, $this->testValidator->validate($test));
    }

    public function testStepCollectionIsRewoundAfterIterating(): void
    {
        $testParser = TestParser::create();

        $test = $testParser->parse([
            'config' => [
                'browser' => 'chrome',
                'url' => 'http://example.com',
            ],
            'step one' => [
                'assertions' => [
                    '$page.title is "Example"',
                ],
            ],
        ]);

        $this->testValidator->validate($test);

        $this->assertSame('step one', $test->getSteps()->key());
    }
}
