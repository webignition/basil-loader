<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Validator\Test;

use webignition\BasilLoader\Validator\InvalidResult;
use webignition\BasilLoader\Validator\InvalidResultInterface;
use webignition\BasilLoader\Validator\ResultInterface;
use webignition\BasilLoader\Validator\ResultType;
use webignition\BasilLoader\Validator\Step\StepValidator;
use webignition\BasilLoader\Validator\ValidResult;
use webignition\BasilModels\Model\PageUrlReference\PageUrlReference;
use webignition\BasilModels\Model\Test\TestInterface;

class TestValidator
{
    public const REASON_BROWSER_EMPTY = 'test-configuration-browser-empty';
    public const REASON_URL_EMPTY = 'test-configuration-url-empty';
    public const REASON_URL_IS_PAGE_URL_REFERENCE = 'test-configuration-url-is-page-url-reference';
    public const REASON_NO_STEPS = 'test-no-steps';
    public const REASON_STEP_INVALID = 'test-step-invalid';
    public const CONTEXT_STEP_NAME = 'step-name';

    private StepValidator $stepValidator;

    public function __construct(StepValidator $stepValidator)
    {
        $this->stepValidator = $stepValidator;
    }

    public static function create(): TestValidator
    {
        return new TestValidator(
            StepValidator::create()
        );
    }

    public function validate(TestInterface $test): ResultInterface
    {
        $pageUrlReference = new PageUrlReference($test->getUrl());
        if ($pageUrlReference->isValid()) {
            return $this->createInvalidResult($test, self::REASON_URL_IS_PAGE_URL_REFERENCE);
        }

        $steps = $test->getSteps();
        if (0 === count($steps)) {
            return $this->createInvalidResult($test, self::REASON_NO_STEPS);
        }

        foreach ($steps as $name => $step) {
            $stepValidationResult = $this->stepValidator->validate($step);

            if ($stepValidationResult instanceof InvalidResultInterface) {
                return $this->createInvalidResult(
                    $test,
                    self::REASON_STEP_INVALID,
                    $stepValidationResult
                )->withContext([
                    self::CONTEXT_STEP_NAME => $name,
                ]);
            }
        }

        $steps->rewind();

        return new ValidResult($test);
    }

    private function createInvalidResult(
        TestInterface $test,
        string $reason,
        ?InvalidResultInterface $invalidResult = null
    ): InvalidResultInterface {
        return new InvalidResult($test, ResultType::TEST, $reason, $invalidResult);
    }
}
