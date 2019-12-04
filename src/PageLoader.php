<?php

declare(strict_types=1);

namespace webignition\BasilLoader;

use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModels\Page\PageInterface;
use webignition\BasilParser\PageParser;

class PageLoader
{
    private $yamlLoader;
    private $pageParser;

    public function __construct(YamlLoader $yamlLoader, PageParser $pageParser)
    {
        $this->yamlLoader = $yamlLoader;
        $this->pageParser = $pageParser;
    }

    public static function createLoader(): PageLoader
    {
        return new PageLoader(
            YamlLoader::createLoader(),
            PageParser::create()
        );
    }

    /**
     * @param string $path
     *
     * @return PageInterface
     *
     * @throws YamlLoaderException
     */
    public function load(string $path): PageInterface
    {
        $data = $this->yamlLoader->loadArray($path);

        return $this->pageParser->parse($data);
    }
}
