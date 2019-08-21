<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilLoader\Tests\Unit\Exception;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;
use webignition\BasilLoader\Exception\YamlLoaderException;

class YamlLoaderExceptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getPathForFileDoesNotExistExceptionDataProvider
     */
    public function testGetPathForFileDoesNotExistException(string $path)
    {
        $parseException = $this->createFileDoesNotExistParseException($path);

        if ($parseException instanceof ParseException) {
            $yamlLoaderException = YamlLoaderException::fromYamlParseException($parseException);

            $this->assertSame($path, $yamlLoaderException->getPath());
        }
    }

    public function getPathForFileDoesNotExistExceptionDataProvider(): array
    {
        return [
            'integer-type path' => [
                'path' => '123',
            ],
        ];
    }

    private function createFileDoesNotExistParseException(string $path): ?ParseException
    {
        $yamlParser = new YamlParser();

        try {
            $yamlParser->parseFile($path);
        } catch (ParseException $parseException) {
            return $parseException;
        }

        return null;
    }
}
