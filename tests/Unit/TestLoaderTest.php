<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit;

use webignition\BasilContextAwareException\ContextAwareExceptionInterface;
use webignition\BasilContextAwareException\ExceptionContext\ExceptionContext;
use webignition\BasilContextAwareException\ExceptionContext\ExceptionContextInterface;
use webignition\BasilLoader\Exception\EmptyTestException;
use webignition\BasilLoader\Exception\InvalidPageException;
use webignition\BasilLoader\Exception\InvalidTestException;
use webignition\BasilLoader\Exception\NonRetrievableImportException;
use webignition\BasilLoader\Exception\ParseException;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilLoader\TestLoader;
use webignition\BasilLoader\Tests\Services\FixturePathFinder;
use webignition\BasilLoader\Validator\InvalidResult;
use webignition\BasilLoader\Validator\ResultType;
use webignition\BasilLoader\Validator\Test\TestValidator;
use webignition\BasilModels\Model\Action\ResolvedAction;
use webignition\BasilModels\Model\Assertion\ResolvedAssertion;
use webignition\BasilModels\Model\DataSet\DataSetCollection;
use webignition\BasilModels\Model\Step\Step;
use webignition\BasilModels\Model\Step\StepCollection;
use webignition\BasilModels\Model\Test\NamedTest;
use webignition\BasilModels\Model\Test\NamedTestInterface;
use webignition\BasilModels\Model\Test\Test;
use webignition\BasilModels\Parser\ActionParser;
use webignition\BasilModels\Parser\AssertionParser;

class TestLoaderTest extends \PHPUnit\Framework\TestCase
{
    private TestLoader $testLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testLoader = TestLoader::createLoader();
    }

    /**
     * @param non-empty-string $path
     *
     * @dataProvider loadSuccessDataProvider
     *
     * @param NamedTestInterface[] $expectedTests
     */
    public function testLoadSuccess(string $path, array $expectedTests): void
    {
        $tests = $this->testLoader->load($path);

        $this->assertEquals($expectedTests, $tests);
    }

    /**
     * @return array<mixed>
     */
    public function loadSuccessDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        return [
            'non-empty' => [
                'path' => FixturePathFinder::find('Test/example.com.verify-open-literal.yml'),
                'expectedTests' => [
                    new NamedTest(
                        new Test('chrome', 'https://example.com', new StepCollection([
                            'verify page is open' => new Step(
                                [],
                                [
                                    $assertionParser->parse('$page.url is "https://example.com"'),
                                ]
                            )
                        ])),
                        FixturePathFinder::find('Test/example.com.verify-open-literal.yml')
                    ),
                ],
            ],
            'import step verify open literal' => [
                'path' => FixturePathFinder::find('Test/example.com.import-step-verify-open-literal.yml'),
                'expectedTests' => [
                    new NamedTest(
                        new Test('chrome', 'https://example.com', new StepCollection([
                            'verify page is open' => new Step(
                                [],
                                [
                                    $assertionParser->parse('$page.url is "https://example.com"'),
                                ]
                            )
                        ])),
                        FixturePathFinder::find('Test/example.com.import-step-verify-open-literal.yml')
                    ),
                ],
            ],
            'import step with data parameters' => [
                'path' => FixturePathFinder::find('Test/example.com.import-step-data-parameters.yml'),
                'expectedTests' => [
                    new NamedTest(
                        new Test('chrome', 'https://example.com', new StepCollection([
                            'data parameters step' => (new Step(
                                [
                                    $actionParser->parse('click $".button"'),
                                ],
                                [
                                    $assertionParser->parse('$".heading" includes $data.expected_title'),
                                ]
                            ))->withData(new DataSetCollection([
                                '0' => [
                                    'expected_title' => 'Foo',
                                ],
                                '1' => [
                                    'expected_title' => 'Bar',
                                ],
                            ]))
                        ])),
                        FixturePathFinder::find('Test/example.com.import-step-data-parameters.yml')
                    ),
                ],
            ],
            'import step with element parameters and imported page' => [
                'path' => FixturePathFinder::find('Test/example.com.import-step-element-parameters.yml'),
                'expectedTests' => [
                    new NamedTest(
                        new Test('chrome', 'https://example.com', new StepCollection([
                            'element parameters step' => new Step(
                                [
                                    new ResolvedAction(
                                        $actionParser->parse('click $elements.button'),
                                        '$".button"'
                                    ),
                                ],
                                [
                                    new ResolvedAssertion(
                                        $assertionParser->parse('$elements.heading includes "example"'),
                                        '$".heading"',
                                        '"example"'
                                    ),
                                ]
                            )
                        ])),
                        FixturePathFinder::find('Test/example.com.import-step-element-parameters.yml')
                    ),
                ],
            ],
            'import step with descendant element parameters' => [
                'path' => FixturePathFinder::find('Test/example.com.descendant-element-parameters.yml'),
                'expectedTests' => [
                    new NamedTest(
                        new Test('chrome', 'https://example.com', new StepCollection([
                            'descendant element parameters step' => new Step(
                                [
                                ],
                                [
                                    new ResolvedAssertion(
                                        $assertionParser->parse('$page_import_name.elements.form exists'),
                                        '$".form"'
                                    ),
                                    new ResolvedAssertion(
                                        $assertionParser->parse('$page_import_name.elements.input exists'),
                                        '$".form" >> $".input"'
                                    ),
                                ]
                            )
                        ])),
                        FixturePathFinder::find('Test/example.com.descendant-element-parameters.yml')
                    ),
                ],
            ],
            'verify open literal with multiple browsers' => [
                'path' => FixturePathFinder::find('Test/example.com.verify-open-literal-multiple-browsers.yml'),
                'expectedTests' => [
                    new NamedTest(
                        new Test('chrome', 'https://example.com', new StepCollection([
                            'verify page is open' => new Step(
                                [],
                                [
                                    $assertionParser->parse('$page.url is "https://example.com"'),
                                ]
                            )
                        ])),
                        FixturePathFinder::find('Test/example.com.verify-open-literal-multiple-browsers.yml')
                    ),
                    new NamedTest(
                        new Test('firefox', 'https://example.com', new StepCollection([
                            'verify page is open' => new Step(
                                [],
                                [
                                    $assertionParser->parse('$page.url is "https://example.com"'),
                                ]
                            )
                        ])),
                        FixturePathFinder::find('Test/example.com.verify-open-literal-multiple-browsers.yml')
                    ),
                ],
            ],
        ];
    }

    /**
     * @param non-empty-string $path
     *
     * @dataProvider loadThrowsNonRetrievableImportExceptionDataProvider
     */
    public function testLoadThrowsNonRetrievableImportException(
        string $path,
        string $expectedFailedImportPath,
        string $expectedExceptionType,
        string $expectedExceptionImportName
    ): void {
        try {
            $this->testLoader->load($path);

            $this->fail('NonRetrievableImportException not thrown');
        } catch (NonRetrievableImportException $nonRetrievableImportException) {
            $this->assertSame($expectedFailedImportPath, $nonRetrievableImportException->getPath());
            $this->assertSame($expectedExceptionType, $nonRetrievableImportException->getType());
            $this->assertSame($expectedExceptionImportName, $nonRetrievableImportException->getName());
            $this->assertInstanceOf(YamlLoaderException::class, $nonRetrievableImportException->getPrevious());
            $this->assertSame($path, $nonRetrievableImportException->getTestPath());
        }
    }

    /**
     * @return array<mixed>
     */
    public function loadThrowsNonRetrievableImportExceptionDataProvider(): array
    {
        return [
            'step' => [
                'path' => FixturePathFinder::find('Test/example.com.import-non-retrievable-step-provider.yml'),
                'expectedFailedImportPath' => sprintf(
                    '%s/Step/file-does-not-exist.yml',
                    str_replace('/Services/../', '/', FixturePathFinder::getBasePath())
                ),
                'expectedExceptionType' => NonRetrievableImportException::TYPE_STEP,
                'expectedExceptionImportName' => 'step_import_name',
            ],
            'page' => [
                'path' => FixturePathFinder::find('Test/example.com.import-non-retrievable-page-provider.yml'),
                'expectedFailedImportPath' => sprintf(
                    '%s/Page/file-does-not-exist.yml',
                    str_replace('/Services/../', '/', FixturePathFinder::getBasePath())
                ),
                'expectedExceptionType' => NonRetrievableImportException::TYPE_PAGE,
                'expectedExceptionImportName' => 'page_import_name',
            ],
            'data provider' => [
                'path' => FixturePathFinder::find('Test/example.com.import-non-retrievable-data-provider.yml'),
                'expectedFailedImportPath' => sprintf(
                    '%s/DataProvider/file-does-not-exist.yml',
                    str_replace('/Services/../', '/', FixturePathFinder::getBasePath())
                ),
                'expectedExceptionType' => NonRetrievableImportException::TYPE_DATA_PROVIDER,
                'expectedExceptionImportName' => 'data_provider_import_name',
            ],
        ];
    }

    public function testLoadThrowsInvalidTestException(): void
    {
        $path = FixturePathFinder::find('Test/invalid.no-steps.yml');

        try {
            $this->testLoader->load($path);

            $this->fail('Exception not thrown');
        } catch (InvalidTestException $invalidTestException) {
            $expectedException = new InvalidTestException(
                $path,
                new InvalidResult(
                    new Test('chrome', 'https://example.com', new StepCollection([])),
                    ResultType::TEST,
                    TestValidator::REASON_NO_STEPS
                )
            );

            $this->assertEquals($expectedException, $invalidTestException);
        }
    }

    /**
     * @param non-empty-string $path
     *
     * @dataProvider loadThrowsParseExceptionDataProvider
     */
    public function testLoadThrowsParseException(
        string $path,
        bool $expectedIsUnparseableTestException,
        bool $expectedIsUnparseableStepException,
        string $expectedExceptionTestPath,
        string $expectedExceptionSubjectPath
    ): void {
        try {
            $this->testLoader->load($path);

            $this->fail('ParseException not thrown');
        } catch (ParseException $parseException) {
            $this->assertSame($expectedIsUnparseableTestException, $parseException->isUnparseableTestException());
            $this->assertSame($expectedIsUnparseableStepException, $parseException->isUnparseableStepException());
            $this->assertSame($expectedExceptionTestPath, $parseException->getTestPath());
            $this->assertSame($expectedExceptionSubjectPath, $parseException->getSubjectPath());
        }
    }

    /**
     * @return array<mixed>
     */
    public function loadThrowsParseExceptionDataProvider(): array
    {
        return [
            'test contains unparseable action' => [
                'path' => FixturePathFinder::find('Test/invalid.empty-action.yml'),
                'expectedIsUnparseableTestException' => true,
                'expectedIsUnparseableStepException' => false,
                'expectedExceptionTestPath' => FixturePathFinder::find('Test/invalid.empty-action.yml'),
                'expectedExceptionSubjectPath' => FixturePathFinder::find('Test/invalid.empty-action.yml')
            ],
            'imported step contains unparseable action' => [
                'path' => FixturePathFinder::find('Test/invalid.import-empty-action.yml'),
                'expectedIsUnparseableTestException' => false,
                'expectedIsUnparseableStepException' => true,
                'expectedExceptionTestPath' => FixturePathFinder::find('Test/invalid.import-empty-action.yml'),
                'expectedExceptionSubjectPath' => FixturePathFinder::find('Step/invalid.empty-action.yml')
            ],
        ];
    }

    public function testLoadThrowsInvalidPageException(): void
    {
        $path = FixturePathFinder::find('Test/invalid.invalid-page.yml');

        try {
            $this->testLoader->load($path);

            $this->fail('InvalidPageException not thrown');
        } catch (InvalidPageException $invalidPageException) {
            $this->assertSame($path, $invalidPageException->getTestPath());
        }
    }

    public function testLoadThrowsEmptyTestException(): void
    {
        $path = FixturePathFinder::find('Empty/empty.yml');
        $this->expectExceptionObject(new EmptyTestException($path));

        $this->testLoader->load($path);
    }

    /**
     * @param non-empty-string $path
     *
     * @dataProvider addTestNameToResolverThrownExceptionDataProvider
     */
    public function testAddTestNameToResolverThrownException(
        string $path,
        ExceptionContextInterface $expectedExceptionContext
    ): void {
        try {
            $this->testLoader->load($path);
        } catch (ContextAwareExceptionInterface $exception) {
            self::assertEquals($expectedExceptionContext, $exception->getExceptionContext());
        }
    }

    /**
     * @return array<mixed>
     */
    public function addTestNameToResolverThrownExceptionDataProvider(): array
    {
        return [
            'test resolver throws unknown item exception' => [
                'path' => FixturePathFinder::find('Test/invalid.missing-data-provider.yml'),
                'expectedExceptionContext' => new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => FixturePathFinder::find(
                        'Test/invalid.missing-data-provider.yml'
                    ),
                    ExceptionContextInterface::KEY_STEP_NAME => 'step referencing missing data provider',
                ]),
            ],
            'test resolver throws unknown element exception' => [
                'path' => FixturePathFinder::find('Test/invalid.missing-element.yml'),
                'expectedExceptionContext' => new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => FixturePathFinder::find(
                        'Test/invalid.missing-element.yml'
                    ),
                    ExceptionContextInterface::KEY_STEP_NAME => 'step referencing undefined element',
                    ExceptionContextInterface::KEY_CONTENT => 'click $elements.element_name'
                ]),
            ],
            'test resolver throws unknown page element exception' => [
                'path' => FixturePathFinder::find('Test/invalid.missing-page-element.yml'),
                'expectedExceptionContext' => new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => FixturePathFinder::find(
                        'Test/invalid.missing-page-element.yml'
                    ),
                    ExceptionContextInterface::KEY_STEP_NAME => 'step referencing missing page element',
                    ExceptionContextInterface::KEY_CONTENT => 'click $page_import_name.elements.missing'
                ]),
            ],
        ];
    }
}
