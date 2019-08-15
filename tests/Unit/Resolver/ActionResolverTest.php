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
use webignition\BasilModel\Identifier\IdentifierCollectionInterface;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Value\EnvironmentValue;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueTypes;
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
    public function testResolvePageElementReferenceIdentifierLeavesActionUnchanged(ActionInterface $action)
    {
        $this->assertSame(
            $action,
            $this->resolver->resolvePageElementReferenceIdentifier($action, new EmptyPageProvider())
        );
    }

    /**
     * @dataProvider resolveLeavesActionUnchangedDataProvider
     */
    public function testResolveElementParameterIdentifierLeavesActionUnchanged(ActionInterface $action)
    {
        $this->assertSame(
            $action,
            $this->resolver->resolveElementParameterIdentifier($action, new IdentifierCollection())
        );
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
        ];
    }

    /**
     * @dataProvider resolvePageElementReferenceIdentifierCreatesNewActionDataProvider
     */
    public function testResolvePageElementReferenceIdentifierCreatesNewAction(
        ActionInterface $action,
        PageProviderInterface $pageProvider,
        ActionInterface $expectedAction
    ) {
        $resolvedIdentifierContainer = $this->resolver->resolvePageElementReferenceIdentifier($action, $pageProvider);

        $this->assertNotSame($action, $resolvedIdentifierContainer);
        $this->assertEquals($expectedAction, $resolvedIdentifierContainer);
    }

    public function resolvePageElementReferenceIdentifierCreatesNewActionDataProvider(): array
    {
        $namedCssElementIdentifier = TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name');

        return [
            'input action with page element reference' => [
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
                            $namedCssElementIdentifier
                        ])
                    )
                ]),
                'expectedAction' => new InputAction(
                    'set page_import_name.elements.element_name to "value"',
                    $namedCssElementIdentifier,
                    LiteralValue::createStringValue('value'),
                    'page_import_name.elements.element_name to "value"'
                ),
            ],
            'interaction action with page element reference' => [
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
                            $namedCssElementIdentifier
                        ])
                    )
                ]),
                'expectedAction' => new InteractionAction(
                    'click page_import_name.elements.element_name',
                    ActionTypes::CLICK,
                    $namedCssElementIdentifier,
                    'page_import_name.elements.element_name'
                ),
            ],
        ];
    }

    /**
     * @dataProvider resolveElementParameterIdentifierCreatesNewActionDataProvider
     */
    public function testResolveElementParameterIdentifierCreatesNewAction(
        ActionInterface $action,
        IdentifierCollectionInterface $identifierCollection,
        ActionInterface $expectedAction
    ) {
        $resolvedIdentifierContainer = $this->resolver->resolveElementParameterIdentifier(
            $action,
            $identifierCollection
        );

        $this->assertNotSame($action, $resolvedIdentifierContainer);
        $this->assertEquals($expectedAction, $resolvedIdentifierContainer);
    }

    public function resolveElementParameterIdentifierCreatesNewActionDataProvider(): array
    {
        $namedCssElementIdentifier = TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name');

        return [
            'input action with element parameter' => [
                'action' => new InputAction(
                    'set $elements.element_name to "value"',
                    new Identifier(
                        IdentifierTypes::ELEMENT_PARAMETER,
                        new ObjectValue(
                            ValueTypes::ELEMENT_PARAMETER,
                            '$elements.element_name',
                            'elements',
                            'element_name'
                        )
                    ),
                    LiteralValue::createStringValue('value'),
                    '$elements.element_name to "value"'
                ),
                'identifierCollection' => new IdentifierCollection([
                    $namedCssElementIdentifier,
                ]),
                'expectedAction' => new InputAction(
                    'set $elements.element_name to "value"',
                    $namedCssElementIdentifier,
                    LiteralValue::createStringValue('value'),
                    '$elements.element_name to "value"'
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
                            'element_name'
                        )
                    ),
                    '$elements.element_name'
                ),
                'identifierCollection' => new IdentifierCollection([
                    $namedCssElementIdentifier,
                ]),
                'expectedAction' => new InteractionAction(
                    'click $elements.element_name',
                    ActionTypes::CLICK,
                    $namedCssElementIdentifier,
                    '$elements.element_name'
                ),
            ],
        ];
    }
}
