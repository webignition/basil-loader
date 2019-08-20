<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace webignition\BasilParser\Tests\Unit\Provider\Step;

use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Provider\Step\StepProvider;

class StepProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testFindStepThrowsUnknownStepException()
    {
        $this->expectException(UnknownStepException::class);
        $this->expectExceptionMessage('Unknown step "step_import_name"');

        $pageProvider = new StepProvider([]);
        $pageProvider->findStep('step_import_name');
    }
}
