<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Factory\Test;

use webignition\BasilParser\DataStructure\Test\Test as TestData;
use webignition\BasilParser\Exception\ContextAwareExceptionInterface;
use webignition\BasilParser\Factory\Test\TestFactory;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContext;
use webignition\BasilParser\Model\Test\TestInterface;
use webignition\BasilParser\Tests\DataProvider\Factory\Test\CreateFromTestDataDataProviderTrait;
use webignition\BasilParser\Tests\DataProvider\Factory\Test\MalformedPageElementReferenceDataProviderTrait;
use webignition\BasilParser\Tests\DataProvider\Factory\Test\NonRetrievableDataProviderDataProviderTrait;
use webignition\BasilParser\Tests\DataProvider\Factory\Test\NonRetrievablePageDataProviderTrait;
use webignition\BasilParser\Tests\DataProvider\Factory\Test\NonRetrievableStepDataProviderTrait;
use webignition\BasilParser\Tests\DataProvider\Factory\Test\UnknownDataProviderDataProviderTrait;
use webignition\BasilParser\Tests\DataProvider\Factory\Test\UnknownPageDataProviderTrait;
use webignition\BasilParser\Tests\DataProvider\Factory\Test\UnknownPageElementDataProviderTrait;
use webignition\BasilParser\Tests\DataProvider\Factory\Test\UnknownStepDataProviderTrait;
use webignition\BasilParser\Tests\Services\FixturePathFinder;
use webignition\BasilParser\Tests\Services\TestFactoryFactory;

class TestFactoryTest extends \PHPUnit\Framework\TestCase
{
    use CreateFromTestDataDataProviderTrait;
    use MalformedPageElementReferenceDataProviderTrait;
    use NonRetrievableDataProviderDataProviderTrait;
    use NonRetrievablePageDataProviderTrait;
    use NonRetrievableStepDataProviderTrait;
    use UnknownDataProviderDataProviderTrait;
    use UnknownPageElementDataProviderTrait;
    use UnknownPageDataProviderTrait;
    use UnknownStepDataProviderTrait;

    /**
     * @var TestFactory
     */
    private $testFactory;

    private $invalidYamlPath = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->testFactory = TestFactoryFactory::create();
        $this->invalidYamlPath = FixturePathFinder::find('invalid-yaml.yml');
    }

    /**
     * @dataProvider createFromTestDataDataProvider
     */
    public function testCreateFromTestData(string $name, TestData $testData, TestInterface $expectedTest)
    {
        $test = $this->testFactory->createFromTestData($name, $testData);

        $this->assertEquals($expectedTest, $test);
    }

    /**
     * @dataProvider createFromTestDataThrowsMalformedPageElementReferenceExceptionDataProvider
     * @dataProvider createFromTestDataThrowsNonRetrievableDataProviderExceptionDataProvider
     * @dataProvider createFromTestDataThrowsNonRetrievablePageExceptionDataProvider
     * @dataProvider createFromTestDataThrowsNonRetrievableStepExceptionDataProvider
     * @dataProvider createFromTestDataThrowsUnknownDataProviderExceptionDataProvider
     * @dataProvider createFromTestDataThrowsUnknownPageElementExceptionDataProvider
     * @dataProvider createFromTestDataThrowsUnknownPageExceptionDataProvider
     * @dataProvider createFromTestDataThrowsUnknownStepExceptionDataProvider
     */
    public function testCreateFromTestDataThrowsException(
        string $name,
        TestData $testData,
        string $expectedException,
        string $expectedExceptionMessage,
        ExceptionContext $expectedExceptionContext
    ) {
        try {
            $this->testFactory->createFromTestData($name, $testData);
        } catch (ContextAwareExceptionInterface $contextAwareException) {
            $this->assertInstanceOf($expectedException, $contextAwareException);
            $this->assertEquals($expectedExceptionMessage, $contextAwareException->getMessage());
            $this->assertEquals($expectedExceptionContext, $contextAwareException->getExceptionContext());
        }
    }
}
