<?php

namespace webignition\BasilParser\PageProvider;

use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Model\Page\PageInterface;

class EmptyPageProvider implements PageProviderInterface
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
