<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Resolver;

use Nyholm\Psr7\Uri;
use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\InputAction;
use webignition\BasilModel\Action\InteractionAction;
use webignition\BasilModel\Identifier\Identifier;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\Value;
use webignition\BasilModel\Value\ValueTypes;
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
            'input action lacking identifier' => [
                'action' => new InputAction(
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
        $resolvedAction = $this->resolver->resolve($action, $pageProvider);

        $this->assertNotSame($action, $resolvedAction);
        $this->assertEquals($expectedAction, $resolvedAction);
    }

    public function resolveCreatesNewActionDataProvider(): array
    {
        return [
            'input action' => [
                'action' => new InputAction(
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
                    ActionTypes::CLICK,
                    new Identifier(
                        IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                        new Value(
                            ValueTypes::STRING,
                            'page_import_name.elements.element_name'
                        )
                    ),
                    '".selector"'
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
        ];
    }
}
