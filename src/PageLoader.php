<?php

namespace webignition\BasilLoader;

use webignition\BasilDataStructure\Page as PageData;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModel\Page\PageInterface;
use webignition\BasilModelFactory\InvalidPageElementIdentifierException;
use webignition\BasilModelFactory\PageFactory;

class PageLoader
{
    private $yamlLoader;
    private $pageFactory;

    public function __construct(YamlLoader $yamlLoader, PageFactory $pageFactory)
    {
        $this->yamlLoader = $yamlLoader;
        $this->pageFactory = $pageFactory;
    }

    public static function createLoader(): PageLoader
    {
        return new PageLoader(
            YamlLoader::createLoader(),
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
        $pageData = new PageData($data);

        return $this->pageFactory->createFromPageData($pageData);
    }
}
