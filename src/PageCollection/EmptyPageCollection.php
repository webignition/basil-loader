<?php

namespace webignition\BasilParser\PageCollection;

use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Model\Page\PageInterface;

class EmptyPageCollection implements PageCollectionInterface
{
    /**
     * @param string $importName
     *
     * @return PageInterface
     *
     * @throws UnknownPageException
     */
    public function findPage(string $importName): PageInterface
    {
        throw new UnknownPageException($importName);
    }
}
