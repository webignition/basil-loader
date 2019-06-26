<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Factory\Test;

use Nyholm\Psr7\Uri;
use webignition\BasilParser\DataStructure\Test\Configuration as ConfigurationData;
use webignition\BasilParser\Factory\Test\ConfigurationFactory;
use webignition\BasilParser\Model\Page\Page;
use webignition\BasilParser\Model\Test\Configuration;
use webignition\BasilParser\Model\Test\ConfigurationInterface;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Page\PopulatedPageProvider;

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
        ConfigurationData $configurationData,
        PageProviderInterface $pageProvider,
        ConfigurationInterface $expectedConfiguration
    ) {
        $configuration = $this->configurationFactory->createFromConfigurationData($configurationData, $pageProvider);

        $this->assertEquals($expectedConfiguration, $configuration);
    }

    public function createFromConfigurationDataDataProvider(): array
    {
        return [
            'empty' => [
                'configurationData' => new ConfigurationData([]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedConfiguration' => new Configuration('', ''),
            ],
            'non-string values' => [
                'configurationData' => new ConfigurationData([
                    ConfigurationData::KEY_BROWSER => true,
                    ConfigurationData::KEY_URL => 3
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedConfiguration' => new Configuration('1', '3'),
            ],
            'string values' => [
                'configurationData' => new ConfigurationData([
                    ConfigurationData::KEY_BROWSER => 'chrome',
                    ConfigurationData::KEY_URL => 'http://example.com',
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedConfiguration' => new Configuration('chrome', 'http://example.com'),
            ],
            'page url reference' => [
                'configurationData' => new ConfigurationData([
                    ConfigurationData::KEY_BROWSER => 'chrome',
                    ConfigurationData::KEY_URL => 'page_import_name.url',
                ]),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(new Uri('http://example.com'), []),
                ]),
                'expectedConfiguration' => new Configuration('chrome', 'http://example.com'),
            ],
        ];
    }
}
