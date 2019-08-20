<?php

namespace webignition\BasilParser\Tests\Services\Provider;

use webignition\BasilModel\Page\PageInterface;
use webignition\BasilModelProvider\Exception\UnknownPageException;
use webignition\BasilModelProvider\Page\PageProviderInterface;

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
