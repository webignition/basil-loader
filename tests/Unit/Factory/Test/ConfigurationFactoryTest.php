<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Factory\Test;

use webignition\BasilParser\Factory\Test\ConfigurationFactory;
use webignition\BasilParser\Model\Test\Configuration;
use webignition\BasilParser\Model\Test\ConfigurationInterface;

class ConfigurationFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigurationFactory
     */
    private $configurationFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configurationFactory = new ConfigurationFactory();
    }

    /**
     * @dataProvider createFromConfigurationDataDataProvider
     */
    public function testCreateFromConfigurationData(
        array $configurationData,
        ConfigurationInterface $expectedConfiguration
    ) {
        $configuration = $this->configurationFactory->createFromConfigurationData($configurationData);

        $this->assertEquals($expectedConfiguration, $configuration);
    }

    public function createFromConfigurationDataDataProvider(): array
    {
        return [
            'empty' => [
                'configurationData' => [],
                'expectedConfiguration' => new Configuration('', ''),
            ],
            'non-string values' => [
                'configurationData' => [
                    ConfigurationFactory::KEY_BROWSER => true,
                    ConfigurationFactory::KEY_URL => 3
                ],
                'expectedConfiguration' => new Configuration('', ''),
            ],
            'string values' => [
                'configurationData' => [
                    ConfigurationFactory::KEY_BROWSER => 'chrome',
                    ConfigurationFactory::KEY_URL => 'http://example.com',
                ],
                'expectedConfiguration' => new Configuration('chrome', 'http://example.com'),
            ],
        ];
    }
}
