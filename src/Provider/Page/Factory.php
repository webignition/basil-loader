<?php

namespace webignition\BasilParser\Provider\Page;

use webignition\BasilParser\Loader\PageLoader;

class Factory
{
    private $pageLoader;

    public function __construct(PageLoader $pageLoader)
    {
        $this->pageLoader = $pageLoader;
    }

    public function createDeferredPageProvider(array $importPaths)
    {
        return new DeferredPageProvider($this->pageLoader, $importPaths);
    }
}
