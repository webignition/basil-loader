<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Factory\Action;

use webignition\BasilParser\Factory\Action\ActionFactory;
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

class ActionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ActionFactory
     */
    private $actionFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actionFactory = new ActionFactory();
    }

    /**
     * @dataProvider createFromActionStringForClickActionDataProvider
     * @dataProvider createFromActionStringForSubmitActionDataProvider
     * @dataProvider createFromActionStringForWaitForActionDataProvider
     */
    public function testCreateFromActionStringForInteractionAction(
        string $actionString,
        InteractionAction $expectedAction
    ) {
        $action = $this->actionFactory->createFromActionString($actionString);

        $this->assertEquals($expectedAction, $action);
    }

    public function createFromActionStringForClickActionDataProvider(): array
    {
        return [
            'click css selector with null position double-quoted' => [
                'actionString' => 'click ".sign-in-form .submit-button"',
                'expectedAction' => new InteractionAction(
                    ActionTypes::CLICK,
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.sign-in-form .submit-button'
                    ),
                    '".sign-in-form .submit-button"'
                ),
            ],
            'click css selector with position double-quoted' => [
                'actionString' => 'click ".sign-in-form .submit-button":3',
                'expectedAction' => new InteractionAction(
                    ActionTypes::CLICK,
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.sign-in-form .submit-button',
                        3
                    ),
                    '".sign-in-form .submit-button":3'
                ),
            ],
            'click css selector unquoted is treated as page model element reference' => [
                'actionString' => 'click .sign-in-form .submit-button',
                'expectedAction' => new InteractionAction(
                    ActionTypes::CLICK,
                    new Identifier(
                        IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                        '.sign-in-form .submit-button'
                    ),
                    '.sign-in-form .submit-button'
                ),
            ],
            'click page model reference' => [
                'actionString' => 'click imported_page_model.elements.element_name',
                'expectedAction' => new InteractionAction(
                    ActionTypes::CLICK,
                    new Identifier(
                        IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                        'imported_page_model.elements.element_name'
                    ),
                    'imported_page_model.elements.element_name'
                ),
            ],
            'click element parameter reference' => [
                'actionString' => 'click $elements.name',
                'expectedAction' => new InteractionAction(
                    ActionTypes::CLICK,
                    new Identifier(
                        IdentifierTypes::ELEMENT_PARAMETER,
                        '$elements.name'
                    ),
                    '$elements.name'
                ),
            ],
            'click with no arguments' => [
                'actionString' => 'click',
                'expectedAction' => new InteractionAction(
                    ActionTypes::CLICK,
                    null,
                    ''
                ),
            ],
        ];
    }

    public function createFromActionStringForSubmitActionDataProvider(): array
    {
        return [
            'submit css selector with null position double-quoted' => [
                'actionString' => 'submit ".sign-in-form"',
                'expectedAction' => new InteractionAction(
                    ActionTypes::SUBMIT,
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.sign-in-form'
                    ),
                    '".sign-in-form"'
                ),
            ],
            'submit css selector with position double-quoted' => [
                'actionString' => 'submit ".sign-in-form":3',
                'expectedAction' => new InteractionAction(
                    ActionTypes::SUBMIT,
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.sign-in-form',
                        3
                    ),
                    '".sign-in-form":3'
                ),
            ],
            'submit css selector unquoted is treated as page model element reference' => [
                'actionString' => 'submit .sign-in-form',
                'expectedAction' => new InteractionAction(
                    ActionTypes::SUBMIT,
                    new Identifier(
                        IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                        '.sign-in-form'
                    ),
                    '.sign-in-form'
                ),
            ],
            'submit page model reference' => [
                'actionString' => 'submit imported_page_model.elements.element_name',
                'expectedAction' => new InteractionAction(
                    ActionTypes::SUBMIT,
                    new Identifier(
                        IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                        'imported_page_model.elements.element_name'
                    ),
                    'imported_page_model.elements.element_name'
                ),
            ],
            'submit element parameter reference' => [
                'actionString' => 'submit $elements.name',
                'expectedAction' => new InteractionAction(
                    ActionTypes::SUBMIT,
                    new Identifier(
                        IdentifierTypes::ELEMENT_PARAMETER,
                        '$elements.name'
                    ),
                    '$elements.name'
                ),
            ],
            'submit no arguments' => [
                'actionString' => 'submit',
                'expectedAction' => new InteractionAction(
                    ActionTypes::SUBMIT,
                    null,
                    ''
                ),
            ],
        ];
    }

    public function createFromActionStringForWaitForActionDataProvider(): array
    {
        return [
            'wait-for css selector with null position double-quoted' => [
                'actionString' => 'wait-for ".sign-in-form .submit-button"',
                'expectedAction' => new InteractionAction(
                    ActionTypes::WAIT_FOR,
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.sign-in-form .submit-button'
                    ),
                    '".sign-in-form .submit-button"'
                ),
            ],
            'wait-for css selector with position double-quoted' => [
                'actionString' => 'wait-for ".sign-in-form .submit-button":3',
                'expectedAction' => new InteractionAction(
                    ActionTypes::WAIT_FOR,
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.sign-in-form .submit-button',
                        3
                    ),
                    '".sign-in-form .submit-button":3'
                ),
            ],
            'wait-for css selector unquoted is treated as page model element reference' => [
                'actionString' => 'wait-for .sign-in-form .submit-button',
                'expectedAction' => new InteractionAction(
                    ActionTypes::WAIT_FOR,
                    new Identifier(
                        IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                        '.sign-in-form .submit-button'
                    ),
                    '.sign-in-form .submit-button'
                ),
            ],
            'wait-for page model reference' => [
                'actionString' => 'wait-for imported_page_model.elements.element_name',
                'expectedAction' => new InteractionAction(
                    ActionTypes::WAIT_FOR,
                    new Identifier(
                        IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                        'imported_page_model.elements.element_name'
                    ),
                    'imported_page_model.elements.element_name'
                ),
            ],
            'wait-for element parameter reference' => [
                'actionString' => 'wait-for $elements.name',
                'expectedAction' => new InteractionAction(
                    ActionTypes::WAIT_FOR,
                    new Identifier(
                        IdentifierTypes::ELEMENT_PARAMETER,
                        '$elements.name'
                    ),
                    '$elements.name'
                ),
            ],
            'wait-for no arguments' => [
                'actionString' => 'wait-for',
                'expectedAction' => new InteractionAction(
                    ActionTypes::WAIT_FOR,
                    null,
                    ''
                ),
            ],
        ];
    }

    /**
     * @dataProvider createFromActionStringForWaitActionDataProvider
     */
    public function testCreateFromActionStringForWaitAction(string $actionString, WaitAction $expectedAction)
    {
        $action = $this->actionFactory->createFromActionString($actionString);

        $this->assertEquals($expectedAction, $action);
    }

    public function createFromActionStringForWaitActionDataProvider(): array
    {
        return [
            'wait 1' => [
                'actionString' => 'wait 1',
                'expectedAction' => new WaitAction('1'),
            ],
            'wait 15' => [
                'actionString' => 'wait 15',
                'expectedAction' => new WaitAction('15'),
            ],
            'wait $data.name' => [
                'actionString' => 'wait $data.name',
                'expectedAction' => new WaitAction('$data.name'),
            ],
            'wait no arguments' => [
                'actionString' => 'wait',
                'expectedAction' => new WaitAction(''),
            ],
        ];
    }

    /**
     * @dataProvider createFromActionStringForNoArgumentsActionDataProvider
     */
    public function testCreateFromActionStringForNoArgumentsAction(
        string $actionString,
        NoArgumentsAction $expectedAction
    ) {
        $action = $this->actionFactory->createFromActionString($actionString);

        $this->assertEquals($expectedAction, $action);
    }

    public function createFromActionStringForNoArgumentsActionDataProvider(): array
    {
        return [
            'reload' => [
                'actionString' => 'reload',
                'expectedAction' => new NoArgumentsAction(ActionTypes::RELOAD, ''),
            ],
            'back' => [
                'actionString' => 'back',
                'expectedAction' => new NoArgumentsAction(ActionTypes::BACK, ''),
            ],
            'forward' => [
                'actionString' => 'forward',
                'expectedAction' => new NoArgumentsAction(ActionTypes::FORWARD, ''),
            ],
        ];
    }

    /**
     * @dataProvider createFromActionStringForInputActionDataProvider
     */
    public function testCreateFromActionStringForInputAction(string $actionString, InputAction $expectedAction)
    {
        $action = $this->actionFactory->createFromActionString($actionString);

        $this->assertEquals($expectedAction, $action);
    }

    public function createFromActionStringForInputActionDataProvider(): array
    {
        return [
            'simple css selector, scalar value' => [
                'actionString' => 'set ".selector" to "value"',
                'expectedAction' => new InputAction(
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.selector'
                    ),
                    new Value(
                        ValueTypes::STRING,
                        'value'
                    ),
                    '".selector" to "value"'
                ),
            ],
            'simple css selector, data parameter value' => [
                'actionString' => 'set ".selector" to $data.name',
                'expectedAction' => new InputAction(
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.selector'
                    ),
                    new Value(
                        ValueTypes::DATA_PARAMETER,
                        '$data.name'
                    ),
                    '".selector" to $data.name'
                ),
            ],
            'simple css selector, element parameter value' => [
                'actionString' => 'set ".selector" to $elements.name',
                'expectedAction' => new InputAction(
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.selector'
                    ),
                    new Value(
                        ValueTypes::ELEMENT_PARAMETER,
                        '$elements.name'
                    ),
                    '".selector" to $elements.name'
                ),
            ],
            'simple css selector, escaped quotes scalar value' => [
                'actionString' => 'set ".selector" to "\"value\""',
                'expectedAction' => new InputAction(
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.selector'
                    ),
                    new Value(
                        ValueTypes::STRING,
                        '"value"'
                    ),
                    '".selector" to "\"value\""'
                ),
            ],
            'css selector includes stop words, scalar value' => [
                'actionString' => 'set ".selector to value" to "value"',
                'expectedAction' => new InputAction(
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.selector to value'
                    ),
                    new Value(
                        ValueTypes::STRING,
                        'value'
                    ),
                    '".selector to value" to "value"'
                ),
            ],
            'simple xpath expression, scalar value' => [
                'actionString' => 'set "//foo" to "value"',
                'expectedAction' => new InputAction(
                    new Identifier(
                        IdentifierTypes::XPATH_EXPRESSION,
                        '//foo'
                    ),
                    new Value(
                        ValueTypes::STRING,
                        'value'
                    ),
                    '"//foo" to "value"'
                ),
            ],
            'xpath expression includes stopwords, scalar value' => [
                'actionString' => 'set "//a[ends-with(@href to value, \".pdf\")]" to "value"',
                'expectedAction' => new InputAction(
                    new Identifier(
                        IdentifierTypes::XPATH_EXPRESSION,
                        '//a[ends-with(@href to value, \".pdf\")]'
                    ),
                    new Value(
                        ValueTypes::STRING,
                        'value'
                    ),
                    '"//a[ends-with(@href to value, \".pdf\")]" to "value"'
                ),
            ],
            'no arguments' => [
                'actionString' => 'set',
                'expectedAction' => new InputAction(
                    null,
                    null,
                    ''
                ),
            ],
            'lacking value' => [
                'actionString' => 'set ".selector" to',
                'expectedAction' => new InputAction(
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.selector'
                    ),
                    null,
                    '".selector" to'
                ),
            ],
            '".selector" lacking "to" keyword' => [
                'actionString' => 'set ".selector" "value"',
                'expectedAction' => new InputAction(
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.selector'
                    ),
                    new Value(
                        ValueTypes::STRING,
                        'value'
                    ),
                    '".selector" "value"'
                ),
            ],
            '".selector to value" lacking "to" keyword' => [
                'actionString' => 'set ".selector to value" "value"',
                'expectedAction' => new InputAction(
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.selector to value'
                    ),
                    new Value(
                        ValueTypes::STRING,
                        'value'
                    ),
                    '".selector to value" "value"'
                ),
            ],
            '".selector" lacking "to" keyword and lacking value' => [
                'actionString' => 'set ".selector"',
                'expectedAction' => new InputAction(
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.selector'
                    ),
                    null,
                    '".selector"'
                ),
            ],
        ];
    }

    public function testCreateFromActionStringForUnrecognisedAction()
    {
        $actionString = 'foo ".selector" to "value';

        $action = $this->actionFactory->createFromActionString($actionString);

        $this->assertInstanceOf(UnrecognisedAction::class, $action);
        $this->assertSame('foo', $action->getType());
        $this->assertFalse($action->isRecognised());
    }

    public function testCreateFromEmptyActionString()
    {
        $actionString = '';

        $action = $this->actionFactory->createFromActionString($actionString);

        $this->assertInstanceOf(UnrecognisedAction::class, $action);
        $this->assertSame('', $action->getType());
        $this->assertFalse($action->isRecognised());
    }
}
