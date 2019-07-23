<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Resolver;

use Nyholm\Psr7\Uri;
use webignition\BasilModel\Assertion\Assertion;
use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Identifier\Identifier;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Value\EnvironmentValue;
use webignition\BasilModel\Value\Value;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Page\PopulatedPageProvider;
use webignition\BasilParser\Resolver\AssertionResolver;
use webignition\BasilParser\Tests\Services\AssertionResolverFactory;

class AssertionResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AssertionResolver
     */
    private $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = AssertionResolverFactory::create();
    }

    /**
     * @dataProvider resolveLeavesActionUnchangedDataProvider
     */
    public function testResolveLeavesActionUnchanged(AssertionInterface $assertion)
    {
        $this->assertSame(
            $assertion,
            $this->resolver->resolve($assertion, new EmptyPageProvider())
        );
    }

    public function resolveLeavesActionUnchangedDataProvider(): array
    {
        return [
            'assertion not implementing IdentifierContainerInterface' => [
                'assertion' => \Mockery::mock(AssertionInterface::class),
            ],
            'assertion lacking identifier' => [
                'assertion' => new Assertion(
                    '',
                    null,
                    ''
                ),
            ],
            'assertion with environment parameter' => [
                'assertion' => new Assertion(
                    '".selector" is $env.KEY',
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        new Value(
                            ValueTypes::STRING,
                            '.selector'
                        )
                    ),
                    AssertionComparisons::IS,
                    new EnvironmentValue(
                        '$env.KEY',
                        'KEY'
                    )
                ),
            ],
        ];
    }

    /**
     * @dataProvider resolveCreatesNewActionDataProvider
     */
    public function testResolveCreatesNewAction(
        AssertionInterface $assertion,
        PageProviderInterface $pageProvider,
        AssertionInterface $expectedAssertion
    ) {
        $resolvedAssertion = $this->resolver->resolve($assertion, $pageProvider);

        $this->assertNotSame($assertion, $resolvedAssertion);
        $this->assertEquals($expectedAssertion, $resolvedAssertion);
    }

    public function resolveCreatesNewActionDataProvider(): array
    {
        return [
            'assertion' => [
                'assertion' => new Assertion(
                    'page_import_name.elements.element_name exists',
                    new Identifier(
                        IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                        new Value(
                            ValueTypes::STRING,
                            'page_import_name.elements.element_name'
                        )
                    ),
                    AssertionComparisons::EXISTS
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
                'expectedAssertion' => new Assertion(
                    'page_import_name.elements.element_name exists',
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        new Value(
                            ValueTypes::STRING,
                            '.selector'
                        )
                    ),
                    AssertionComparisons::EXISTS
                ),
            ],
        ];
    }
}
