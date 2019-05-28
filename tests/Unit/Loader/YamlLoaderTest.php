<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Loader;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;
use webignition\BasilParser\Exception\YamlLoaderException;
use webignition\BasilParser\Loader\YamlLoader;

class YamlLoaderTest extends \PHPUnit\Framework\TestCase
{
    public function testLoadArrayYamlParserThrowsException()
    {
        $path = 'file.yml';
        $exceptionMessage = 'exception message content';

        $parseException = new ParseException($exceptionMessage);

        $yamlParser = \Mockery::mock(YamlParser::class);
        $yamlParser
            ->shouldReceive('parseFile')
            ->with($path)
            ->andThrow($parseException);

        $yamlLoader = new YamlLoader($yamlParser);

        $this->expectException(YamlLoaderException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $yamlLoader->loadArray($path);
    }

    public function testLoadArrayThrowsDataIsNotAnArrayException()
    {
        $path = 'file.yml';



        $yamlParser = \Mockery::mock(YamlParser::class);
        $yamlParser
            ->shouldReceive('parseFile')
            ->with($path)
            ->andReturn(1);

        $yamlLoader = new YamlLoader($yamlParser);

        $this->expectException(YamlLoaderException::class);
        $this->expectExceptionMessage(YamlLoaderException::MESSAGE_DATA_IS_NOT_AN_ARRAY);
        $this->expectExceptionCode(YamlLoaderException::CODE_DATA_IS_NOT_AN_ARRAY);

        $yamlLoader->loadArray($path);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        \Mockery::close();
    }
}
