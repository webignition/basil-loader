<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit\Validator\Test;

use webignition\BasilLoader\Validator\InvalidResult;
use webignition\BasilLoader\Validator\InvalidResultInterface;
use webignition\BasilLoader\Validator\ResultType;
use webignition\BasilLoader\Validator\Step\StepValidator;
use webignition\BasilLoader\Validator\Test\ConfigurationValidator;
use webignition\BasilLoader\Validator\Test\TestValidator;
use webignition\BasilLoader\Validator\ValidResult;
use webignition\BasilModels\Model\Step\Step;
use webignition\BasilModels\Model\Step\StepCollection;
use webignition\BasilModels\Model\Test\Configuration;
use webignition\BasilModels\Model\Test\Test;
use webignition\BasilModels\Model\Test\TestInterface;
use webignition\BasilParser\Test\TestParser;

class TestValidatorTest extends \PHPUnit\Framework\TestCase
{
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
    public function validateNotValidDataProvider(): array
    {
        $configurationWithEmptyBrowser = new Configuration('', '');
        $testWithInvalidConfiguration = new Test($configurationWithEmptyBrowser, new StepCollection([]));

        $validConfiguration = new Configuration('chrome', 'http://example.com/');

        $testWithNoSteps = new Test($validConfiguration, new StepCollection([]));

        $invalidStep = new Step([], []);
        $testWithInvalidStep = new Test($validConfiguration, new StepCollection([
            'invalid step name' => $invalidStep,
        ]));

        return [
            'invalid configuration' => [
                'test' => $testWithInvalidConfiguration,
                'expectedResult' => new InvalidResult(
                    $testWithInvalidConfiguration,
                    ResultType::TEST,
                    TestValidator::REASON_CONFIGURATION_INVALID,
                    new InvalidResult(
                        $configurationWithEmptyBrowser,
                        ResultType::TEST_CONFIGURATION,
                        ConfigurationValidator::REASON_BROWSER_EMPTY
                    )
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
