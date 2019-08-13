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
use webignition\BasilModel\Identifier\IdentifierCollection;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Value\EnvironmentValue;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Page\PopulatedPageProvider;
use webignition\BasilParser\Resolver\ActionResolver;
use webignition\BasilParser\Tests\Services\TestIdentifierFactory;

class ActionResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ActionResolver
     */
    private $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = ActionResolver::createResolver();
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
                'action' => new WaitAction('wait 30', LiteralValue::createStringValue('30')),
            ],
            'input action lacking identifier' => [
                'action' => new InputAction(
                    'set to "value"',
                    null,
                    LiteralValue::createStringValue('value'),
                    'to "value"'
                ),
            ],
            'input action with css selector' => [
                'action' => new InputAction(
                    'set ".selector" to "value"',
                    TestIdentifierFactory::createCssElementIdentifier('.selector'),
                    LiteralValue::createStringValue('value'),
                    '".selector" to "value"'
                ),
            ],
            'input action with xpath expression' => [
                'action' => new InputAction(
                    'set "//foo" to "value"',
                    TestIdentifierFactory::createXpathElementIdentifier('//foo'),
                    LiteralValue::createStringValue('value'),
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
                    LiteralValue::createStringValue('value'),
                    '$elements.element_name to "value"'
                ),
            ],
            'input action with environment parameter value' => [
                'action' => new InputAction(
                    'set ".selector" to $env.KEY',
                    TestIdentifierFactory::createCssElementIdentifier('.selector'),
                    new EnvironmentValue(
                        '$env.KEY',
                        'KEY'
                    ),
                    '".selector" to $env.KEY'
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
                    TestIdentifierFactory::createCssElementIdentifier('.selector'),
                    '".selector"'
                ),
            ],
            'interaction action with xpath expression' => [
                'action' => new InteractionAction(
                    'click "/foo"',
                    ActionTypes::CLICK,
                    TestIdentifierFactory::createXpathElementIdentifier('//foo'),
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
                        IdentifierTypes::PAGE_ELEMENT_REFERENCE,
                        new ObjectValue(
                            ValueTypes::PAGE_ELEMENT_REFERENCE,
                            'page_import_name.elements.element_name',
                            'page_import_name',
                            'element_name'
                        )
                    ),
                    LiteralValue::createStringValue('value'),
                    'page_import_name.elements.element_name to "value"'
                ),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        new IdentifierCollection([
                            TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name')
                        ])
                    )
                ]),
                'expectedAction' => new InputAction(
                    'set page_import_name.elements.element_name to "value"',
                    TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name'),
                    LiteralValue::createStringValue('value'),
                    'page_import_name.elements.element_name to "value"'
                ),
            ],
            'interaction action' => [
                'action' => new InteractionAction(
                    'click page_import_name.elements.element_name',
                    ActionTypes::CLICK,
                    new Identifier(
                        IdentifierTypes::PAGE_ELEMENT_REFERENCE,
                        new ObjectValue(
                            ValueTypes::PAGE_ELEMENT_REFERENCE,
                            'page_import_name.elements.element_name',
                            'page_import_name',
                            'element_name'
                        )
                    ),
                    'page_import_name.elements.element_name'
                ),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        new IdentifierCollection([
                            TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name')
                        ])
                    )
                ]),
                'expectedAction' => new InteractionAction(
                    'click page_import_name.elements.element_name',
                    ActionTypes::CLICK,
                    TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name'),
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
                IdentifierTypes::PAGE_ELEMENT_REFERENCE,
                new ObjectValue(
                    ValueTypes::PAGE_ELEMENT_REFERENCE,
                    'page_import_name.elements.element_name',
                    'page_import_name',
                    'element_name'
                )
            ),
            LiteralValue::createStringValue('value'),
            'page_import_name.elements.unknown_element_name to "value"'
        );

        $pageProvider = new PopulatedPageProvider([
            'page_import_name' => new Page(
                new Uri('http://example.com/'),
                new IdentifierCollection()
            )
        ]);

        $this->expectException(UnknownPageElementException::class);
        $this->expectExceptionMessage('Unknown page element "element_name" in page "page_import_name"');

        $this->resolver->resolve($action, $pageProvider);
    }
}
