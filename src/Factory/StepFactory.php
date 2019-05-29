<?php

namespace webignition\BasilParser\Factory;

use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Factory\Action\ActionFactory;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContextInterface;
use webignition\BasilParser\Model\Step\Step;
use webignition\BasilParser\Model\Step\StepInterface;
use webignition\BasilParser\Provider\Page\PageProviderInterface;

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

    public function __construct(ActionFactory $actionFactory, AssertionFactory $assertionFactory)
    {
        $this->actionFactory = $actionFactory;
        $this->assertionFactory = $assertionFactory;
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
        $assertions = [];

        $actionString = '';
        $assertionString = '';

        try {
            foreach ($actionStrings as $actionString) {
                if ('string' === gettype($actionString)) {
                    $actionString = trim($actionString);

                    if ('' !== $actionString) {
                        $actions[] = $this->actionFactory->createFromActionString($actionString, $pageProvider);
                    }
                }
            }

            foreach ($assertionStrings as $assertionString) {
                if ('string' === gettype($assertionString)) {
                    $assertionString = trim($assertionString);

                    if ('' !== $assertionString) {
                        $assertions[] = $this->assertionFactory->createFromAssertionString(
                            $assertionString,
                            $pageProvider
                        );
                    }
                }
            }
        } catch (MalformedPageElementReferenceException |
            NonRetrievablePageException |
            UnknownPageElementException $contextAwareException
        ) {
            $contextAwareException->applyExceptionContext([
                ExceptionContextInterface::KEY_CONTENT => $assertionString !== '' ? $assertionString : $actionString,
            ]);

            throw $contextAwareException;
        }

        return new Step($actions, $assertions);
    }
}
