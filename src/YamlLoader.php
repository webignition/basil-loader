<?php

declare(strict_types=1);

namespace webignition\BasilLoader;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;
use webignition\BasilLoader\Exception\YamlLoaderException;

class YamlLoader
{
    private $yamlParser;

    public function __construct(YamlParser $yamlParser)
    {
        $this->yamlParser = $yamlParser;
    }

    public static function createLoader(): YamlLoader
    {
        return new YamlLoader(
            new YamlParser()
        );
    }

    /**
     * @param string $path
     *
     * @return mixed
     *
     * @throws YamlLoaderException
     */
    public function loadArray(string $path)
    {
        try {
            $data = $this->yamlParser->parseFile($path);
        } catch (ParseException $parseException) {
            throw YamlLoaderException::fromYamlParseException($parseException, $path);
        }

        if (is_string($data) && '' === trim($data)) {
            $data = null;
        }

        if (null === $data) {
            $data = [];
        }

        if (!is_array($data)) {
            throw YamlLoaderException::createDataIsNotAnArrayException($path);
        }

        return $data;
    }
}
