<?php

namespace webignition\BasilParser\Loader;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;
use webignition\BasilParser\Exception\YamlLoaderException;

class YamlLoader
{
    private $yamlParser;

    public function __construct(YamlParser $yamlParser)
    {
        $this->yamlParser = $yamlParser;
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
            throw YamlLoaderException::fromYamlParseException($parseException);
        }

        if (!is_array($data)) {
            throw YamlLoaderException::createDataIsNotAnArrayException();
        }

        return $data;
    }
}
