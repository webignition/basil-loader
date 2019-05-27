<?php

namespace webignition\BasilParser\Factory;

use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Factory\Action\ActionFactory;
use webignition\BasilParser\Model\Step\Step;
use webignition\BasilParser\Model\Step\StepInterface;
use webignition\BasilParser\PageProvider\PageProviderInterface;

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

    /**
     * @param array $stepData
     * @param PageProviderInterface $pageProvider
     *
     * @return StepInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws NonRetrievablePageException
     */
    public function createFromStepData(array $stepData, PageProviderInterface $pageProvider): StepInterface
    {
        $actionStrings = $stepData[self::KEY_ACTIONS] ?? [];
        $assertionStrings = $stepData[self::KEY_ASSERTIONS] ?? [];

        $actionStrings = is_array($actionStrings) ? $actionStrings : [];
        $assertionStrings = is_array($assertionStrings) ? $assertionStrings : [];

        $actions = [];
        foreach ($actionStrings as $actionString) {
            if ('string' === gettype($actionString)) {
                $actionString = trim($actionString);

                if ('' !== $actionString) {
                    $actions[] = $this->actionFactory->createFromActionString($actionString, $pageProvider);
                }
            }
        }

        $assertions = [];
        foreach ($assertionStrings as $assertionString) {
            if ('string' === gettype($assertionString)) {
                $assertionString = trim($assertionString);

                if ('' !== $assertionString) {
                    $assertions[] = $this->assertionFactory->createFromAssertionString($assertionString, $pageProvider);
                }
            }
        }

        return new Step($actions, $assertions);
    }
}
