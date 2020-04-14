<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit;

use webignition\BasilDataValidator\ResultType;
use webignition\BasilDataValidator\Test\ConfigurationValidator;
use webignition\BasilDataValidator\Test\TestValidator;
use webignition\BasilLoader\Exception\InvalidPageException;
use webignition\BasilLoader\Exception\InvalidTestException;
use webignition\BasilLoader\Exception\NonRetrievableImportException;
use webignition\BasilLoader\Exception\ParseException;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilLoader\TestLoader;
use webignition\BasilLoader\Tests\Services\FixturePathFinder;
use webignition\BasilModels\Action\InteractionAction;
use webignition\BasilModels\Assertion\Assertion;
use webignition\BasilModels\Assertion\ComparisonAssertion;
use webignition\BasilModels\DataSet\DataSetCollection;
use webignition\BasilModels\Step\Step;
use webignition\BasilModels\Test\Configuration;
use webignition\BasilModels\Test\Test;
use webignition\BasilModels\Test\TestInterface;
use webignition\BasilValidationResult\InvalidResult;

class TestLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TestLoader
     */
    private $testLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testLoader = TestLoader::createLoader();
    }

    /**
     * @dataProvider loadSuccessDataProvider
     */
    public function testLoadSuccess(string $path, TestInterface $expectedTest)
    {
        $test = $this->testLoader->load($path);

        $this->assertEquals($expectedTest, $test);
    }

    public function loadSuccessDataProvider(): array
    {
        return [
            'non-empty' => [
                'path' => FixturePathFinder::find('Test/example.com.verify-open-literal.yml'),
                'expectedTest' => (new Test(
                    new Configuration('chrome', 'https://example.com'),
                    [
                        'verify page is open' => new Step(
                            [],
                            [
                                new ComparisonAssertion(
                                    '$page.url is "https://example.com"',
                                    '$page.url',
                                    'is',
                                    '"https://example.com"'
                                ),
                            ]
                        )
                    ]
                ))->withPath(FixturePathFinder::find('Test/example.com.verify-open-literal.yml')),
            ],
            'import step verify open literal' => [
                'path' => FixturePathFinder::find('Test/example.com.import-step-verify-open-literal.yml'),
                'expectedTest' => (new Test(
                    new Configuration('chrome', 'https://example.com'),
                    [
                        'verify page is open' => new Step(
                            [],
                            [
                                new ComparisonAssertion(
                                    '$page.url is "https://example.com"',
                                    '$page.url',
                                    'is',
                                    '"https://example.com"'
                                ),
                            ]
                        )
                    ]
                ))->withPath(FixturePathFinder::find('Test/example.com.import-step-verify-open-literal.yml')),
            ],
            'import step with data parameters' => [
                'path' => FixturePathFinder::find('Test/example.com.import-step-data-parameters.yml'),
                'expectedTest' => (new Test(
                    new Configuration('chrome', 'https://example.com'),
                    [
                        'data parameters step' => (new Step(
                            [
                                new InteractionAction(
                                    'click $".button"',
                                    'click',
                                    '$".button"',
                                    '$".button"'
                                )
                            ],
                            [
                                new ComparisonAssertion(
                                    '$".heading" includes $data.expected_title',
                                    '$".heading"',
                                    'includes',
                                    '$data.expected_title'
                                ),
                            ]
                        ))->withData(new DataSetCollection([
                            '0' => [
                                'expected_title' => 'Foo',
                            ],
                            '1' => [
                                'expected_title' => 'Bar',
                            ],
                        ]))
                    ]
                ))->withPath(FixturePathFinder::find('Test/example.com.import-step-data-parameters.yml')),
            ],
            'import step with element parameters and imported page' => [
                'path' => FixturePathFinder::find('Test/example.com.import-step-element-parameters.yml'),
                'expectedTest' => (new Test(
                    new Configuration('chrome', 'https://example.com'),
                    [
                        'element parameters step' => new Step(
                            [
                                new InteractionAction(
                                    'click $elements.button',
                                    'click',
                                    '$elements.button',
                                    '$".button"'
                                )
                            ],
                            [
                                new ComparisonAssertion(
                                    '$elements.heading includes "example"',
                                    '$".heading"',
                                    'includes',
                                    '"example"'
                                ),
                            ]
                        )
                    ]
                ))->withPath(FixturePathFinder::find('Test/example.com.import-step-element-parameters.yml')),
            ],
            'import step with descendant element parameters' => [
                'path' => FixturePathFinder::find('Test/example.com.descendant-element-parameters.yml'),
                'expectedTest' => (new Test(
                    new Configuration('chrome', 'https://example.com'),
                    [
                        'descendant element parameters step' => new Step(
                            [
                            ],
                            [
                                new Assertion(
                                    '$page_import_name.elements.form exists',
                                    '$".form"',
                                    'exists'
                                ),
                                new Assertion(
                                    '$page_import_name.elements.input exists',
                                    '$".form" >> $".input"',
                                    'exists'
                                ),
                            ]
                        )
                    ]
                ))->withPath(FixturePathFinder::find('Test/example.com.descendant-element-parameters.yml')),
            ],
        ];
    }

    /**
     * @dataProvider loadThrowsNonRetrievableImportExceptionDataProvider
     */
    public function testLoadThrowsNonRetrievableImportException(
        string $path,
        string $expectedFailedImportPath,
        string $expectedExceptionType,
        string $expectedExceptionImportName
    ) {
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

    public function testLoadThrowsInvalidTestException()
    {
        $path = FixturePathFinder::find('Empty/empty.yml');

        try {
            $this->testLoader->load($path);

            $this->fail('Exception not thrown');
        } catch (InvalidTestException $invalidTestException) {
            $expectedException = new InvalidTestException(
                $path,
                new InvalidResult(
                    (new Test(
                        new Configuration('', ''),
                        []
                    ))->withPath($path),
                    ResultType::TEST,
                    TestValidator::REASON_CONFIGURATION_INVALID,
                    new InvalidResult(
                        new Configuration('', ''),
                        ResultType::TEST_CONFIGURATION,
                        ConfigurationValidator::REASON_BROWSER_EMPTY
                    )
                )
            );

            $this->assertEquals($expectedException, $invalidTestException);
        }
    }

    /**
     * @dataProvider loadThrowsParseExceptionDataProvider
     */
    public function testLoadThrowsParseException(
        string $path,
        bool $expectedIsUnparseableTestException,
        bool $expectedIsUnparseableStepException,
        string $expectedExceptionTestPath,
        string $expectedExceptionSubjectPath
    ) {
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

    public function testLoadThrowsInvalidPageException()
    {
        $path = FixturePathFinder::find('Test/invalid.invalid-page.yml');

        try {
            $this->testLoader->load($path);

            $this->fail('InvalidPageException not thrown');
        } catch (InvalidPageException $invalidPageException) {
            $this->assertSame($path, $invalidPageException->getTestPath());
        }
    }
}
