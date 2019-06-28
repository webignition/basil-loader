<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\DataStructure\Test;

use webignition\BasilParser\DataStructure\Step;
use webignition\BasilParser\DataStructure\Test\Configuration;
use webignition\BasilParser\DataStructure\Test\Imports;
use webignition\BasilParser\DataStructure\Test\Test;
use webignition\BasilParser\PathResolver\PathResolver;
use webignition\BasilParser\Tests\Services\PathResolverFactory;

class TestTest extends \PHPUnit\Framework\TestCase
{
    public function testGetConfiguration()
    {
        $testDataStructure = new Test(PathResolverFactory::create(), []);

        $this->assertInstanceOf(Configuration::class, $testDataStructure->getConfiguration());
    }

    public function testGetImports()
    {
        $testDataStructure = new Test(PathResolverFactory::create(), []);

        $this->assertInstanceOf(Imports::class, $testDataStructure->getImports());
    }

    /**
     * @dataProvider getStepsDataProvider
     */
    public function testGetSteps(Test $testDataStructure, array $expectedSteps)
    {
        $this->assertEquals($expectedSteps, $testDataStructure->getSteps());
    }

    public function getStepsDataProvider(): array
    {
        return [
            'empty' => [
                'testDataStructure' => new Test(PathResolverFactory::create(), []),
                'expectedSteps' => [],
            ],
            'configuration and imports are excluded' => [
                'testDataStructure' => new Test(
                    PathResolverFactory::create(),
                    [
                        Test::KEY_CONFIGURATION => [
                            Configuration::KEY_URL => 'http://example.com',
                            Configuration::KEY_BROWSER => 'chrome',
                        ],
                        Test::KEY_IMPORTS => [
                            Imports::KEY_STEPS => [],
                            Imports::KEY_PAGES => [],
                            Imports::KEY_DATA_PROVIDERS => [],
                        ],
                        'step 1' => [
                            Step::KEY_ACTIONS => [
                                'click ".foo"',
                            ],
                            Step::KEY_ASSERTIONS => [
                                '".foo" exists',
                            ],
                        ],
                    ]
                ),
                'expectedSteps' => [
                    'step 1' => new Step([
                        Step::KEY_ACTIONS => [
                            'click ".foo"',
                        ],
                        Step::KEY_ASSERTIONS => [
                            '".foo" exists',
                        ],
                    ]),
                ],
            ],
        ];
    }
}
