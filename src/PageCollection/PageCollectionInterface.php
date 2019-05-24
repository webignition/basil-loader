<?php

namespace webignition\BasilParser\PageCollection;

use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Model\Page\PageInterface;

interface PageCollectionInterface
{
    /**
     * @param string $importName
     *
     * @return PageInterface
     *
     * @throws UnknownPageException
     * @throws NonRetrievablePageException
     */
    public function findPage(string $importName): PageInterface;
}
