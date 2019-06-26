<?php

namespace webignition\BasilParser\Loader;

use webignition\BasilParser\DataStructure\Page as PageData;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Exception\YamlLoaderException;
use webignition\BasilParser\Factory\PageFactory;
use webignition\BasilParser\Model\Page\PageInterface;

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
     * @throws NonRetrievablePageException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     */
    public function load(string $path): PageInterface
    {
        $data = $this->yamlLoader->loadArray($path);
        $pageData = new PageData($data);

        return $this->pageFactory->createFromPageData($pageData);
    }
}
