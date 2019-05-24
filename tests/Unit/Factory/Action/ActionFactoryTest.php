<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Factory\Action;

use Nyholm\Psr7\Uri;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Factory\Action\ActionFactory;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InputAction;
use webignition\BasilParser\Model\Action\InteractionAction;
use webignition\BasilParser\Model\Action\NoArgumentsAction;
use webignition\BasilParser\Model\Action\UnrecognisedAction;
use webignition\BasilParser\Model\Action\WaitAction;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;
use webignition\BasilParser\Model\Page\Page;
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
        array $pages,
        InteractionAction $expectedAction
    ) {
        $action = $this->actionFactory->createFromActionString($actionString, $pages);

        $this->assertEquals($expectedAction, $action);
    }

    public function createFromActionStringForClickActionDataProvider(): array
    {
        return [
            'click css selector with null position double-quoted' => [
                'actionString' => 'click ".sign-in-form .submit-button"',
                'pages' => [],
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
                'pages' => [],
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
            'click page model reference' => [
                'actionString' => 'click page_import_name.elements.element_name',
                'pages' => [
                    'page_import_name' => new Page(
                        new Uri('http://example.com'),
                        [
                            'element_name' => new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.selector'
                            )
                        ]
                    ),
                ],
                'expectedAction' => new InteractionAction(
                    ActionTypes::CLICK,
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.selector'
                    ),
                    'page_import_name.elements.element_name'
                ),
            ],
            'click element parameter reference' => [
                'actionString' => 'click $elements.name',
                'pages' => [],
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
                'pages' => [],
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
                'pages' => [],
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
                'pages' => [],
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
            'submit page model reference' => [
                'actionString' => 'submit page_import_name.elements.element_name',
                'pages' => [
                    'page_import_name' => new Page(
                        new Uri('http://example.com'),
                        [
                            'element_name' => new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.selector'
                            )
                        ]
                    ),
                ],
                'expectedAction' => new InteractionAction(
                    ActionTypes::SUBMIT,
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.selector'
                    ),
                    'page_import_name.elements.element_name'
                ),
            ],
            'submit element parameter reference' => [
                'actionString' => 'submit $elements.name',
                'pages' => [],
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
                'pages' => [],
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
                'pages' => [],
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
                'pages' => [],
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
            'wait-for page model reference' => [
                'actionString' => 'wait-for page_import_name.elements.element_name',
                'pages' => [
                    'page_import_name' => new Page(
                        new Uri('http://example.com'),
                        [
                            'element_name' => new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.selector'
                            )
                        ]
                    ),
                ],
                'expectedAction' => new InteractionAction(
                    ActionTypes::WAIT_FOR,
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.selector'
                    ),
                    'page_import_name.elements.element_name'
                ),
            ],
            'wait-for element parameter reference' => [
                'actionString' => 'wait-for $elements.name',
                'pages' => [],
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
                'pages' => [],
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

    /**
     * @dataProvider createFromActionStringThrowsPageElementExceptionDataProvider
     */
    public function testCreateFromActionStringThrowsPageElementException(
        string $actionString,
        array $pages,
        string $expectedException,
        string $expectedExceptionMessage
    ) {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->actionFactory->createFromActionString($actionString, $pages);
    }

    public function createFromActionStringThrowsPageElementExceptionDataProvider(): array
    {
        return [
            'click malformed page element reference' => [
                'actionString' => 'click invalid-page-element-reference',
                'pages' => [],
                'expectedException' => MalformedPageElementReferenceException::class,
                'expectedExceptionMessage' => 'Malformed page element reference "invalid-page-element-reference"',
            ],
            'click action unknown page' => [
                'actionString' => 'click page_import_name.elements.element_name',
                'pages' => [],
                'expectedException' => UnknownPageException::class,
                'expectedExceptionMessage' => 'Unknown page "page_import_name',
            ],
            'click action unknown page element' => [
                'actionString' => 'click page_import_name.elements.element_name',
                'pages' => [
                    'page_import_name' => new Page(new Uri('http://example.com'), []),
                ],
                'expectedException' => UnknownPageElementException::class,
                'expectedExceptionMessage' => 'Unknown page element "element_name" in page "page_import_name"',
            ],
            'set malformed page element reference' => [
                'actionString' => 'set invalid-page-element-reference to "value"',
                'pages' => [],
                'expectedException' => MalformedPageElementReferenceException::class,
                'expectedExceptionMessage' => 'Malformed page element reference "invalid-page-element-reference"',
            ],
            'set action unknown page' => [
                'actionString' => 'set page_import_name.elements.element_name to "value"',
                'pages' => [],
                'expectedException' => UnknownPageException::class,
                'expectedExceptionMessage' => 'Unknown page "page_import_name',
            ],
            'set action unknown page element' => [
                'actionString' => 'set page_import_name.elements.element_name to "value"',
                'pages' => [
                    'page_import_name' => new Page(new Uri('http://example.com'), []),
                ],
                'expectedException' => UnknownPageElementException::class,
                'expectedExceptionMessage' => 'Unknown page element "element_name" in page "page_import_name"',
            ],
            'submit malformed page element reference' => [
                'actionString' => 'submit invalid-page-element-reference',
                'pages' => [],
                'expectedException' => MalformedPageElementReferenceException::class,
                'expectedExceptionMessage' => 'Malformed page element reference "invalid-page-element-reference"',
            ],
            'submit action unknown page' => [
                'actionString' => 'submit page_import_name.elements.element_name',
                'pages' => [],
                'expectedException' => UnknownPageException::class,
                'expectedExceptionMessage' => 'Unknown page "page_import_name',
            ],
            'submit action unknown page element' => [
                'actionString' => 'submit page_import_name.elements.element_name',
                'pages' => [
                    'page_import_name' => new Page(new Uri('http://example.com'), []),
                ],
                'expectedException' => UnknownPageElementException::class,
                'expectedExceptionMessage' => 'Unknown page element "element_name" in page "page_import_name"',
            ],
            'wait-for malformed page element reference' => [
                'actionString' => 'wait-for invalid-page-element-reference',
                'pages' => [],
                'expectedException' => MalformedPageElementReferenceException::class,
                'expectedExceptionMessage' => 'Malformed page element reference "invalid-page-element-reference"',
            ],
            'wait-for action unknown page' => [
                'actionString' => 'wait-for page_import_name.elements.element_name',
                'pages' => [],
                'expectedException' => UnknownPageException::class,
                'expectedExceptionMessage' => 'Unknown page "page_import_name',
            ],
            'wait-for action unknown page element' => [
                'actionString' => 'wait-for page_import_name.elements.element_name',
                'pages' => [
                    'page_import_name' => new Page(new Uri('http://example.com'), []),
                ],
                'expectedException' => UnknownPageElementException::class,
                'expectedExceptionMessage' => 'Unknown page element "element_name" in page "page_import_name"',
            ],
            'click css selector unquoted is treated as page model element reference' => [
                'actionString' => 'click .sign-in-form .submit-button',
                'pages' => [],
                'expectedException' => MalformedPageElementReferenceException::class,
                'expectedExceptionMessage' => 'Malformed page element reference ".sign-in-form .submit-button"',
            ],
            'submit css selector unquoted is treated as page model element reference' => [
                'actionString' => 'submit .sign-in-form',
                'pages' => [],
                'expectedException' => MalformedPageElementReferenceException::class,
                'expectedExceptionMessage' => 'Malformed page element reference ".sign-in-form"',
            ],
            'wait-for css selector unquoted is treated as page model element reference' => [
                'actionString' => 'wait-for .sign-in-form .submit-button',
                'pages' => [],
                'expectedException' => MalformedPageElementReferenceException::class,
                'expectedExceptionMessage' => 'Malformed page element reference ".sign-in-form .submit-button"',
            ],
        ];
    }
}
