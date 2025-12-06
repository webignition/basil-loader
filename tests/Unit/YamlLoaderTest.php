<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilLoader\Tests\Services\FixturePathFinder;
use webignition\BasilLoader\YamlLoader;

class YamlLoaderTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        \Mockery::close();
    }

    public function testLoadArrayYamlParserThrowsException(): void
    {
        $path = 'file.yml';
        $exceptionMessage = 'exception message content';

        $parseException = new ParseException($exceptionMessage);

        $yamlParser = \Mockery::mock(YamlParser::class);
        $yamlParser
            ->shouldReceive('parseFile')
            ->with($path)
            ->andThrow($parseException)
        ;

        $yamlLoader = new YamlLoader($yamlParser);

        $this->expectException(YamlLoaderException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $yamlLoader->loadArray($path);
    }

    public function testLoadArrayThrowsDataIsNotAnArrayException(): void
    {
        $path = 'file.yml';

        $yamlParser = \Mockery::mock(YamlParser::class);
        $yamlParser
            ->shouldReceive('parseFile')
            ->with($path)
            ->andReturn(1)
        ;

        $yamlLoader = new YamlLoader($yamlParser);

        $this->expectException(YamlLoaderException::class);
        $this->expectExceptionMessage(YamlLoaderException::MESSAGE_DATA_IS_NOT_AN_ARRAY);
        $this->expectExceptionCode(YamlLoaderException::CODE_DATA_IS_NOT_AN_ARRAY);

        $yamlLoader->loadArray($path);
    }

    /**
     * @dataProvider loadArrayWithEmptyContentDataProvider
     */
    public function testLoadArrayWithEmptyContent(string $path): void
    {
        $yamlLoader = YamlLoader::createLoader();

        $data = $yamlLoader->loadArray($path);

        $this->assertSame([], $data);
    }

    /**
     * @return array<mixed>
     */
    public function loadArrayWithEmptyContentDataProvider(): array
    {
        return [
            'empty' => [
                'path' => FixturePathFinder::find('Empty/empty.yml'),
            ],
            'whitespace' => [
                'path' => FixturePathFinder::find('Empty/whitespace.yml'),
            ],
            'null, canonical' => [
                'path' => FixturePathFinder::find('Empty/null-canonical.yml'),
            ],
            'null, non-canonical' => [
                'path' => FixturePathFinder::find('Empty/null-non-canonical.yml'),
            ],
        ];
    }
}
