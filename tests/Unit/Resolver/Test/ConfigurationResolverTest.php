<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Resolver\Test;

use Nyholm\Psr7\Uri;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Test\Configuration;
use webignition\BasilModel\Test\ConfigurationInterface;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Page\PopulatedPageProvider;
use webignition\BasilParser\Resolver\Test\ConfigurationResolver;
use webignition\BasilParser\Tests\Services\TestConfigurationResolverFactory;

class ConfigurationResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigurationResolver
     */
    private $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = TestConfigurationResolverFactory::create();
    }

    /**
     * @dataProvider resolveDataProvider
     */
    public function testResolve(
        ConfigurationInterface $configuration,
        PageProviderInterface $pageProvider,
        ConfigurationInterface $expectedConfiguration
    ) {
        $resolvedConfiguration = $this->resolver->resolve($configuration, $pageProvider);

        $this->assertEquals($expectedConfiguration, $resolvedConfiguration);
    }

    public function resolveDataProvider(): array
    {
        return [
            'empty' => [
                'configuration' => new Configuration('', ''),
                'pageProvider' => new EmptyPageProvider(),
                'expectedConfiguration' => new Configuration('', ''),
            ],
            'browser only' => [
                'configuration' => new Configuration('chrome', ''),
                'pageProvider' => new EmptyPageProvider(),
                'expectedConfiguration' => new Configuration('chrome', ''),
            ],
            'literal url' => [
                'configuration' => new Configuration('chrome', 'http://example.com/'),
                'pageProvider' => new EmptyPageProvider(),
                'expectedConfiguration' => new Configuration('chrome', 'http://example.com/'),
            ],
            'well-formed page url reference' => [
                'configuration' => new Configuration('chrome', 'page_import_name.url'),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(new Uri('http://page.example.com/'), []),
                ]),
                'expectedConfiguration' => new Configuration('chrome', 'http://page.example.com/'),
            ],
        ];
    }
}
