<?php

namespace webignition\BasilParser\Provider\Page;

use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Loader\PageLoader;
use webignition\BasilParser\Loader\YamlLoaderException;
use webignition\BasilParser\Model\Page\PageInterface;

class DeferredPageProvider implements PageProviderInterface
{
    private $pageLoader;
    private $importPaths;
    private $pages = [];

    public function __construct(PageLoader $pageLoader, array $importPaths)
    {
        $this->pageLoader = $pageLoader;
        $this->importPaths = $importPaths;
    }

    /**
     * @param string $importName
     *
     * @return PageInterface
     *
     * @throws NonRetrievablePageException
     * @throws UnknownPageException
     */
    public function findPage(string $importName): PageInterface
    {
        $page = $this->pages[$importName] ?? null;

        if (null === $page) {
            $page = $this->retrievePage($importName);
            $this->pages[$importName] = $page;
        }

        return $page;
    }

    /**
     * @param string $importName
     *
     * @return PageInterface
     *
     * @throws NonRetrievablePageException
     * @throws UnknownPageException
     */
    private function retrievePage(string $importName): PageInterface
    {
        $importPath = $this->importPaths[$importName] ?? null;

        if (null === $importPath) {
            throw new UnknownPageException($importName);
        }

        try {
            return $this->pageLoader->load($importPath);
        } catch (YamlLoaderException $yamlLoaderException) {
            throw new NonRetrievablePageException($importName, $importPath, $yamlLoaderException);
        }
    }
}
