<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Factory\Action;

use webignition\BasilParser\Factory\Action\ActionFactory;
use webignition\BasilParser\Factory\Action\ActionOnlyActionFactory;
use webignition\BasilParser\Factory\Action\InputActionFactory;
use webignition\BasilParser\Factory\Action\InteractionActionFactory;
use webignition\BasilParser\Factory\Action\WaitActionFactory;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InputAction;
use webignition\BasilParser\Model\Action\InputActionInterface;
use webignition\BasilParser\Model\Action\InteractionAction;
use webignition\BasilParser\Model\Action\WaitAction;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierInterface;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;
use webignition\BasilParser\Model\Value\Value;
use webignition\BasilParser\Model\Value\ValueInterface;
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

        $interactionActionFactory = new InteractionActionFactory();
        $waitActionFactory = new WaitActionFactory();
        $actionOnlyActionFactory = new ActionOnlyActionFactory();
        $inputActionFactory = new InputActionFactory();

        $this->actionFactory = new ActionFactory([
            $interactionActionFactory,
            $waitActionFactory,
            $actionOnlyActionFactory,
            $inputActionFactory,
        ]);
    }

    /**
     * @dataProvider createFromActionStringForValidClickActionDataProvider
     * @dataProvider createFromActionStringForValidSubmitActionDataProvider
     * @dataProvider createFromActionStringForValidWaitForActionDataProvider
     */
    public function testCreateFromActionStringForValidInteractionAction(
        string $actionString,
        string $expectedVerb,
        IdentifierInterface $expectedIdentifier
    ) {
        $action = $this->actionFactory->createFromActionString($actionString);

        $this->assertInstanceOf(InteractionAction::class, $action);
        $this->assertSame($expectedVerb, $action->getVerb());

        if ($action instanceof InteractionAction) {
            $this->assertEquals($expectedIdentifier, $action->getIdentifier());
        }
    }

    public function createFromActionStringForValidClickActionDataProvider(): array
    {
        return [
            'click css selector with null position double-quoted' => [
                'actionString' => 'click ".sign-in-form .submit-button"',
                'expectedVerb' => ActionTypes::CLICK,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.sign-in-form .submit-button'
                ),
            ],
            'click css selector with position double-quoted' => [
                'actionString' => 'click ".sign-in-form .submit-button":3',
                'expectedVerb' => ActionTypes::CLICK,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.sign-in-form .submit-button',
                    3
                ),
            ],
            'click css selector unquoted is treated as page model element reference' => [
                'actionString' => 'click .sign-in-form .submit-button',
                'expectedVerb' => ActionTypes::CLICK,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                    '.sign-in-form .submit-button'
                ),
            ],
            'click page model reference' => [
                'actionString' => 'click imported_page_model.elements.element_name',
                'expectedVerb' => ActionTypes::CLICK,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                    'imported_page_model.elements.element_name'
                ),
            ],
            'click element parameter reference' => [
                'actionString' => 'click $elements.name',
                'expectedVerb' => ActionTypes::CLICK,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::ELEMENT_PARAMETER,
                    '$elements.name'
                ),
            ],
        ];
    }

    public function createFromActionStringForValidSubmitActionDataProvider(): array
    {
        return [
            'submit css selector with null position double-quoted' => [
                'actionString' => 'submit ".sign-in-form .submit-button"',
                'expectedVerb' => ActionTypes::SUBMIT,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.sign-in-form .submit-button'
                ),
            ],
            'submit css selector with position double-quoted' => [
                'actionString' => 'submit ".sign-in-form .submit-button":3',
                'expectedVerb' => ActionTypes::SUBMIT,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.sign-in-form .submit-button',
                    3
                ),
            ],
            'submit css selector unquoted is treated as page model element reference' => [
                'actionString' => 'submit .sign-in-form .submit-button',
                'expectedVerb' => ActionTypes::SUBMIT,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                    '.sign-in-form .submit-button'
                ),
            ],
            'submit page model reference' => [
                'actionString' => 'submit imported_page_model.elements.element_name',
                'expectedVerb' => ActionTypes::SUBMIT,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                    'imported_page_model.elements.element_name'
                ),
            ],
            'submit element parameter reference' => [
                'actionString' => 'submit $elements.name',
                'expectedVerb' => ActionTypes::SUBMIT,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::ELEMENT_PARAMETER,
                    '$elements.name'
                ),
            ],
        ];
    }

    public function createFromActionStringForValidWaitForActionDataProvider(): array
    {
        return [
            'wait-for css selector with null position double-quoted' => [
                'actionString' => 'wait-for ".sign-in-form .submit-button"',
                'expectedVerb' => ActionTypes::WAIT_FOR,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.sign-in-form .submit-button'
                ),
            ],
            'wait-for css selector with position double-quoted' => [
                'actionString' => 'wait-for ".sign-in-form .submit-button":3',
                'expectedVerb' => ActionTypes::WAIT_FOR,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.sign-in-form .submit-button',
                    3
                ),
            ],
            'wait-for css selector unquoted is treated as page model element reference' => [
                'actionString' => 'wait-for .sign-in-form .submit-button',
                'expectedVerb' => ActionTypes::WAIT_FOR,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                    '.sign-in-form .submit-button'
                ),
            ],
            'wait-for page model reference' => [
                'actionString' => 'wait-for imported_page_model.elements.element_name',
                'expectedVerb' => ActionTypes::WAIT_FOR,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                    'imported_page_model.elements.element_name'
                ),
            ],
            'wait-for element parameter reference' => [
                'actionString' => 'wait-for $elements.name',
                'expectedVerb' => ActionTypes::WAIT_FOR,
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::ELEMENT_PARAMETER,
                    '$elements.name'
                ),
            ],
        ];
    }

    /**
     * @dataProvider createFromActionStringForValidWaitActionDataProvider
     */
    public function testCreateFromActionStringForValidWaitAction(
        string $actionString,
        string $expectedVerb,
        string $expectedDuration
    ) {
        $action = $this->actionFactory->createFromActionString($actionString);

        $this->assertInstanceOf(WaitAction::class, $action);
        $this->assertSame($expectedVerb, $action->getVerb());

        if ($action instanceof WaitAction) {
            $this->assertSame($expectedDuration, $action->getDuration());
        }
    }

    public function createFromActionStringForValidWaitActionDataProvider(): array
    {
        return [
            'wait 1' => [
                'actionString' => 'wait 1',
                'expectedVerb' => ActionTypes::WAIT,
                'expectedDuration' => '1',
            ],
            'wait 15' => [
                'actionString' => 'wait 15',
                'expectedVerb' => ActionTypes::WAIT,
                'expectedDuration' => '15',
            ],
            'wait $data.name' => [
                'actionString' => 'wait $data.name',
                'expectedVerb' => ActionTypes::WAIT,
                'expectedDuration' => '$data.name',
            ],
        ];
    }

    /**
     * @dataProvider createFromActionStringForValidWaitActionDataProvider
     */
    public function testCreateFromActionStringForValidActionOnlyAction(string $actionString, string $expectedVerb)
    {
        $action = $this->actionFactory->createFromActionString($actionString);

        $this->assertInstanceOf(WaitAction::class, $action);
        $this->assertSame($expectedVerb, $action->getVerb());
    }

    public function createFromActionStringForValidActionOnlyActionDataProvider(): array
    {
        return [
            'reload' => [
                'actionString' => 'reload',
                'expectedVerb' => ActionTypes::RELOAD,
            ],
            'back' => [
                'actionString' => 'back',
                'expectedVerb' => ActionTypes::BACK,
            ],
            'forward' => [
                'actionString' => 'forward',
                'expectedVerb' => ActionTypes::FORWARD,
            ],
        ];
    }

    /**
     * @dataProvider createFromActionStringForValidInputActionDataProvider
     */
    public function testCreateFromActionStringForValidInputAction(
        string $actionString,
        IdentifierInterface $expectedIdentifier,
        ValueInterface $expectedValue
    ) {
        $action = $this->actionFactory->createFromActionString($actionString);

        $this->assertInstanceOf(InputActionInterface::class, $action);
        $this->assertEquals(ActionTypes::SET, $action->getVerb());

        if ($action instanceof InputAction) {
            $this->assertEquals($expectedIdentifier, $action->getIdentifier());
            $this->assertEquals($expectedValue, $action->getValue());
        }
    }

    public function createFromActionStringForValidInputActionDataProvider(): array
    {
        return [
            'simple css selector, scalar value' => [
                'actionString' => 'set ".selector" to "value"',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedValue' => new Value(
                    ValueTypes::STRING,
                    'value'
                ),
            ],
            'simple css selector, data parameter value' => [
                'actionString' => 'set ".selector" to $data.name',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedValue' => new Value(
                    ValueTypes::DATA_PARAMETER,
                    '$data.name'
                ),
            ],
            'simple css selector, element parameter value' => [
                'actionString' => 'set ".selector" to $elements.name',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedValue' => new Value(
                    ValueTypes::ELEMENT_PARAMETER,
                    '$elements.name'
                ),
            ],
            'simple css selector, escaped quotes scalar value' => [
                'actionString' => 'set ".selector" to "\"value\""',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedValue' => new Value(
                    ValueTypes::STRING,
                    '"value"'
                ),
            ],
            'css selector includes stop words, scalar value' => [
                'actionString' => 'set ".selector to value" to "value"',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector to value'
                ),
                'expectedValue' => new Value(
                    ValueTypes::STRING,
                    'value'
                ),
            ],
            'simple xpath expression, scalar value' => [
                'actionString' => 'set "//foo" to "value"',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::XPATH_EXPRESSION,
                    '//foo'
                ),
                'expectedValue' => new Value(
                    ValueTypes::STRING,
                    'value'
                ),
            ],
            'xpath expression includes stopwords, scalar value' => [
                'actionString' => 'set "//a[ends-with(@href to value, ".pdf")]" to "value"',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::XPATH_EXPRESSION,
                    '//a[ends-with(@href to value, ".pdf")]'
                ),
                'expectedValue' => new Value(
                    ValueTypes::STRING,
                    'value'
                ),
            ],
        ];
    }
}
