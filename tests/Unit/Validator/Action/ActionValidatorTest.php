<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Validator\Action;

use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InputAction;
use webignition\BasilParser\Model\Action\InteractionAction;
use webignition\BasilParser\Model\Action\NoArgumentsAction;
use webignition\BasilParser\Model\Action\UnrecognisedAction;
use webignition\BasilParser\Model\Action\WaitAction;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;
use webignition\BasilParser\Model\Value\Value;
use webignition\BasilParser\Model\Value\ValueTypes;
use webignition\BasilParser\Validator\Action\ActionValidator;

class ActionValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ActionValidator
     */
    private $actionValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actionValidator = new ActionValidator();
    }

    /**
     * @dataProvider validateNotValidDataProvider
     */
    public function testValidateNotValid(ActionInterface $action)
    {
        $this->assertFalse($this->actionValidator->validate($action));
    }

    public function validateNotValidDataProvider(): array
    {
        return [
            'input action lacking identifier' => [
                'action' => new InputAction(
                    null,
                    new Value(
                        ValueTypes::STRING,
                        'foo'
                    ),
                    ' to "foo"'
                ),
            ],
            'input action lacking value' => [
                'action' => new InputAction(
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.selector'
                    ),
                    null,
                    '".selector" to'
                ),
            ],
            'input action with identifier and value, lacking "to" keyword' => [
                'action' => new InputAction(
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.selector'
                    ),
                    new Value(
                        ValueTypes::STRING,
                        'foo'
                    ),
                    '".selector" "foo"'
                ),
            ],
            'input action with identifier containing "to" keyword and value, lacking "to" keyword' => [
                'action' => new InputAction(
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.selector to value'
                    ),
                    new Value(
                        ValueTypes::STRING,
                        'foo'
                    ),
                    '".selector to value" "foo"'
                ),
            ],
            'input action with identifier and value containing "to" keyword, lacking "to" keyword' => [
                'action' => new InputAction(
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.selector'
                    ),
                    new Value(
                        ValueTypes::STRING,
                        'foo to value'
                    ),
                    '".selector" "foo to value"'
                ),
            ],
            'interaction action without identifier' => [
                'action' => new InteractionAction(
                    ActionTypes::CLICK,
                    null,
                    ''
                ),
            ],
            'wait action without duration' => [
                'action' => new WaitAction(
                    ''
                ),
            ],
            'unrecognised action' => [
                'action' => new UnrecognisedAction(
                    'foo',
                    ''
                ),
            ],
            'empty action' => [
                'action' => new UnrecognisedAction(
                    '',
                    ''
                ),
            ],
        ];
    }

    /**
     * @dataProvider validateIsValidDataProvider
     */
    public function testValidateIsValid(ActionInterface $action)
    {
        $this->assertTrue($this->actionValidator->validate($action));
    }

    public function validateIsValidDataProvider(): array
    {
        return [
            'input action' => [
                'action' => new InputAction(
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.selector'
                    ),
                    new Value(
                        ValueTypes::STRING,
                        'foo'
                    ),
                    '".selector" to "foo"'
                ),
            ],
            'interaction action without identifier' => [
                'action' => new InteractionAction(
                    ActionTypes::CLICK,
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.selector'
                    ),
                    '".selector"'
                ),
            ],
            'reload action, no arguments' => [
                'action' => new NoArgumentsAction(
                    ActionTypes::RELOAD,
                    ''
                ),
            ],
            'reload action, has arguments' => [
                'action' => new NoArgumentsAction(
                    ActionTypes::RELOAD,
                    'foo bar'
                ),
            ],
            'back action, no arguments' => [
                'action' => new NoArgumentsAction(
                    ActionTypes::BACK,
                    ''
                ),
            ],
            'back action, has arguments' => [
                'action' => new NoArgumentsAction(
                    ActionTypes::BACK,
                    'foo bar'
                ),
            ],
            'forward action, no arguments' => [
                'action' => new NoArgumentsAction(
                    ActionTypes::FORWARD,
                    ''
                ),
            ],
            'forward action, has arguments' => [
                'action' => new NoArgumentsAction(
                    ActionTypes::FORWARD,
                    'foo bar'
                ),
            ],
            'wait action' => [
                'action' => new WaitAction(
                    '5'
                ),
            ],
        ];
    }
}
