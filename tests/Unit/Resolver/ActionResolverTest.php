<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Resolver;

use Nyholm\Psr7\Uri;
use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\InputAction;
use webignition\BasilModel\Action\InteractionAction;
use webignition\BasilModel\Action\WaitAction;
use webignition\BasilModel\Identifier\Identifier;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\Value;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Page\PopulatedPageProvider;
use webignition\BasilParser\Resolver\ActionResolver;
use webignition\BasilParser\Tests\Services\ActionResolverFactory;

class ActionResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ActionResolver
     */
    private $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = ActionResolverFactory::create();
    }

    /**
     * @dataProvider resolveLeavesActionUnchangedDataProvider
     */
    public function testResolveLeavesActionUnchanged(ActionInterface $action)
    {
        $this->assertSame($action, $this->resolver->resolve($action, new EmptyPageProvider()));
    }

    public function resolveLeavesActionUnchangedDataProvider(): array
    {
        return [
            'wait action' => [
                'action' => new WaitAction('wait 30', '30'),
            ],
            'input action lacking identifier' => [
                'action' => new InputAction(
                    'set to "value"',
                    null,
                    new Value(
                        ValueTypes::STRING,
                        'value'
                    ),
                    'to "value"'
                ),
            ],
            'input action with css selector' => [
                'action' => new InputAction(
                    'set ".selector" to "value"',
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        new Value(
                            ValueTypes::STRING,
                            '.selector'
                        )
                    ),
                    new Value(
                        ValueTypes::STRING,
                        'value'
                    ),
                    '".selector" to "value"'
                ),
            ],
            'input action with xpath expression' => [
                'action' => new InputAction(
                    'set "//foo" to "value"',
                    new Identifier(
                        IdentifierTypes::XPATH_EXPRESSION,
                        new Value(
                            ValueTypes::STRING,
                            '//foo'
                        )
                    ),
                    new Value(
                        ValueTypes::STRING,
                        'value'
                    ),
                    '"//foo" to "value"'
                ),
            ],
            'input action with element parameter' => [
                'action' => new InputAction(
                    'set $elements.element_name to "value"',
                    new Identifier(
                        IdentifierTypes::ELEMENT_PARAMETER,
                        new ObjectValue(
                            ValueTypes::ELEMENT_PARAMETER,
                            '$elements.element_name',
                            'elements',
                            'name'
                        )
                    ),
                    new Value(
                        ValueTypes::STRING,
                        'value'
                    ),
                    '$elements.element_name to "value"'
                ),
            ],
            'input action with page object parameter' => [
                'action' => new InputAction(
                    'set $page.title to "value"',
                    new Identifier(
                        IdentifierTypes::PAGE_OBJECT_PARAMETER,
                        new ObjectValue(
                            ValueTypes::PAGE_OBJECT_PROPERTY,
                            '$page.title',
                            'page',
                            'title'
                        )
                    ),
                    new Value(
                        ValueTypes::STRING,
                        'value'
                    ),
                    '$page.title to "value"'
                ),
            ],
            'input action with browser object parameter' => [
                'action' => new InputAction(
                    'set $browser.size to "value"',
                    new Identifier(
                        IdentifierTypes::BROWSER_OBJECT_PARAMETER,
                        new ObjectValue(
                            ValueTypes::BROWSER_OBJECT_PROPERTY,
                            '$browser.size',
                            'browser',
                            'size'
                        )
                    ),
                    new Value(
                        ValueTypes::STRING,
                        'value'
                    ),
                    '$browser.size to "value"'
                ),
            ],
            'interaction action lacking identifier' => [
                'action' => new InteractionAction(
                    'click',
                    ActionTypes::CLICK,
                    null,
                    ''
                ),
            ],
            'interaction action with css selector' => [
                'action' => new InteractionAction(
                    'click ".selector"',
                    ActionTypes::CLICK,
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        new Value(
                            ValueTypes::STRING,
                            '.selector'
                        )
                    ),
                    '".selector"'
                ),
            ],
            'interaction action with xpath expression' => [
                'action' => new InteractionAction(
                    'click "/foo"',
                    ActionTypes::CLICK,
                    new Identifier(
                        IdentifierTypes::XPATH_EXPRESSION,
                        new Value(
                            ValueTypes::STRING,
                            '//foo'
                        )
                    ),
                    '"//foo"'
                ),
            ],
            'interaction action with element parameter' => [
                'action' => new InteractionAction(
                    'click $elements.element_name',
                    ActionTypes::CLICK,
                    new Identifier(
                        IdentifierTypes::ELEMENT_PARAMETER,
                        new ObjectValue(
                            ValueTypes::ELEMENT_PARAMETER,
                            '$elements.element_name',
                            'elements',
                            'name'
                        )
                    ),
                    '$elements.element_name'
                ),
            ],
            'interaction action with page object parameter' => [
                'action' => new InteractionAction(
                    'click $page.title',
                    ActionTypes::CLICK,
                    new Identifier(
                        IdentifierTypes::PAGE_OBJECT_PARAMETER,
                        new ObjectValue(
                            ValueTypes::PAGE_OBJECT_PROPERTY,
                            '$page.title',
                            'page',
                            'title'
                        )
                    ),
                    '$page.title'
                ),
            ],
            'interaction action with browser object parameter' => [
                'action' => new InteractionAction(
                    'click $browser.size',
                    ActionTypes::CLICK,
                    new Identifier(
                        IdentifierTypes::BROWSER_OBJECT_PARAMETER,
                        new ObjectValue(
                            ValueTypes::BROWSER_OBJECT_PROPERTY,
                            '$browser.size',
                            'browser',
                            'size'
                        )
                    ),
                    '$browser.size'
                ),
            ],
        ];
    }

    /**
     * @dataProvider resolveCreatesNewActionDataProvider
     */
    public function testResolveCreatesNewAction(
        ActionInterface $action,
        PageProviderInterface $pageProvider,
        ActionInterface $expectedAction
    ) {
        $resolvedIdentifierContainer = $this->resolver->resolve($action, $pageProvider);

        $this->assertNotSame($action, $resolvedIdentifierContainer);
        $this->assertEquals($expectedAction, $resolvedIdentifierContainer);
    }

    public function resolveCreatesNewActionDataProvider(): array
    {
        return [
            'input action' => [
                'action' => new InputAction(
                    'set page_import_name.elements.element_name to "value"',
                    new Identifier(
                        IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                        new Value(
                            ValueTypes::STRING,
                            'page_import_name.elements.element_name'
                        )
                    ),
                    new Value(
                        ValueTypes::STRING,
                        'value'
                    ),
                    'page_import_name.elements.element_name to "value"'
                ),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        [
                            'element_name' => new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                new Value(
                                    ValueTypes::STRING,
                                    '.selector'
                                )
                            )
                        ]
                    )
                ]),
                'expectedAction' => new InputAction(
                    'set page_import_name.elements.element_name to "value"',
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        new Value(
                            ValueTypes::STRING,
                            '.selector'
                        )
                    ),
                    new Value(
                        ValueTypes::STRING,
                        'value'
                    ),
                    'page_import_name.elements.element_name to "value"'
                ),
            ],
            'interaction action' => [
                'action' => new InteractionAction(
                    'click page_import_name.elements.element_name',
                    ActionTypes::CLICK,
                    new Identifier(
                        IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                        new Value(
                            ValueTypes::STRING,
                            'page_import_name.elements.element_name'
                        )
                    ),
                    'page_import_name.elements.element_name'
                ),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        [
                            'element_name' => new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                new Value(
                                    ValueTypes::STRING,
                                    '.selector'
                                )
                            )
                        ]
                    )
                ]),
                'expectedAction' => new InteractionAction(
                    'click page_import_name.elements.element_name',
                    ActionTypes::CLICK,
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        new Value(
                            ValueTypes::STRING,
                            '.selector'
                        )
                    ),
                    'page_import_name.elements.element_name'
                ),
            ],
        ];
    }

    public function testThrowsUnknownPageElementException()
    {
        $action = new InputAction(
            'set page_import_name.elements.unknown_element_name to "value"',
            new Identifier(
                IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                new Value(
                    ValueTypes::STRING,
                    'page_import_name.elements.unknown_element_name'
                )
            ),
            new Value(
                ValueTypes::STRING,
                'value'
            ),
            'page_import_name.elements.unknown_element_name to "value"'
        );

        $pageProvider = new PopulatedPageProvider([
            'page_import_name' => new Page(
                new Uri('http://example.com/'),
                [
                    'element_name' => new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        new Value(
                            ValueTypes::STRING,
                            '.selector'
                        )
                    )
                ]
            )
        ]);

        $this->expectException(UnknownPageElementException::class);

        $this->resolver->resolve($action, $pageProvider);
    }
}
