<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Factory\Test;

use webignition\BasilContextAwareException\ContextAwareExceptionInterface;
use webignition\BasilContextAwareException\ExceptionContext\ExceptionContext;
use webignition\BasilModel\Test\TestInterface;
use webignition\BasilDataStructure\Test\Test as TestData;
use webignition\BasilParser\Factory\Test\TestFactory;
use webignition\BasilParser\Tests\DataProvider\Factory\Test\CreateFromTestDataDataProviderTrait;
use webignition\BasilParser\Tests\DataProvider\Factory\Test\MalformedPageElementReferenceDataProviderTrait;
use webignition\BasilParser\Tests\Services\FixturePathFinder;
use webignition\BasilParser\Tests\Services\TestFactoryFactory;

class TestFactoryTest extends \PHPUnit\Framework\TestCase
{
    use CreateFromTestDataDataProviderTrait;
    use MalformedPageElementReferenceDataProviderTrait;

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
    public function testCreateFromTestDataSuccess(string $name, TestData $testData, TestInterface $expectedTest)
    {
        $test = $this->testFactory->createFromTestData($name, $testData);

        $this->assertEquals($expectedTest, $test);
    }

    /**
     * @dataProvider createFromTestDataThrowsMalformedPageElementReferenceExceptionDataProvider
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
