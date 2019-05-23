<?php

namespace webignition\BasilParser\Factory;

use webignition\BasilParser\Factory\Action\ActionFactory;
use webignition\BasilParser\Model\Step\Step;
use webignition\BasilParser\Model\Step\StepInterface;

class StepFactory
{
    const KEY_ACTIONS = 'actions';
    const KEY_ASSERTIONS = 'assertions';

    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var AssertionFactory
     */
    private $assertionFactory;

    public function __construct()
    {
        $this->actionFactory = new ActionFactory();
        $this->assertionFactory = new AssertionFactory();
    }

    public function createFromStepData(array $stepData): StepInterface
    {
        $actionStrings = $stepData[self::KEY_ACTIONS] ?? [];
        $assertionStrings = $stepData[self::KEY_ASSERTIONS] ?? [];

        $actionStrings = is_array($actionStrings) ? $actionStrings : [];
        $assertionStrings = is_array($assertionStrings) ? $assertionStrings : [];

        $actions = [];
        foreach ($actionStrings as $actionString) {
            if ('string' === gettype($actionString)) {
                $actions[] = $this->actionFactory->createFromActionString($actionString);
            }
        }

        $assertions = [];
        foreach ($assertionStrings as $assertionString) {
            if ('string' === gettype($assertionString)) {
                $assertions[] = $this->assertionFactory->createFromAssertionString($assertionString);
            }
        }

        return new Step($actions, $assertions);
    }
}
