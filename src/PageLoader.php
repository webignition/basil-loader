<?php

declare(strict_types=1);

namespace webignition\BasilLoader;

use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModel\Page\PageInterface;
use webignition\BasilModelFactory\InvalidPageElementIdentifierException;
use webignition\BasilModelFactory\PageFactory;
use webignition\BasilParser\PageParser;

class PageLoader
{
    private $yamlLoader;
    private $pageParser;
    private $pageFactory;

    public function __construct(YamlLoader $yamlLoader, PageParser $pageParser, PageFactory $pageFactory)
    {
        $this->yamlLoader = $yamlLoader;
        $this->pageParser = $pageParser;
        $this->pageFactory = $pageFactory;
    }

    public static function createLoader(): PageLoader
    {
        return new PageLoader(
            YamlLoader::createLoader(),
            PageParser::create(),
            PageFactory::create()
        );
    }

    /**
     * @param string $path
     *
     * @return PageInterface
     *
     * @throws YamlLoaderException
     * @throws InvalidPageElementIdentifierException
     */
    public function load(string $path): PageInterface
    {
        $data = $this->yamlLoader->loadArray($path);
        $pageData = $this->pageParser->parse($data);

        return $this->pageFactory->createFromPageData($pageData);
    }
}
