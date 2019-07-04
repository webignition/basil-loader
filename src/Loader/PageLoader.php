<?php

namespace webignition\BasilParser\Loader;

use webignition\BasilModel\Page\PageInterface;
use webignition\BasilParser\DataStructure\Page as PageData;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\YamlLoaderException;
use webignition\BasilParser\Factory\PageFactory;

class PageLoader
{
    private $yamlLoader;
    private $pageFactory;

    public function __construct(YamlLoader $yamlLoader, PageFactory $pageFactory)
    {
        $this->yamlLoader = $yamlLoader;
        $this->pageFactory = $pageFactory;
    }

    /**
     * @param string $path
     *
     * @return PageInterface
     *
     * @throws YamlLoaderException
     * @throws MalformedPageElementReferenceException
     */
    public function load(string $path): PageInterface
    {
        $data = $this->yamlLoader->loadArray($path);
        $pageData = new PageData($data);

        return $this->pageFactory->createFromPageData($pageData);
    }
}
