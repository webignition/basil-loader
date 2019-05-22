<?php

namespace webignition\BasilParser\Loader;

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
     */
    public function load(string $path): PageInterface
    {
        $data = $this->yamlLoader->loadArray($path);

        return $this->pageFactory->createFromPageData($data);
    }
}
