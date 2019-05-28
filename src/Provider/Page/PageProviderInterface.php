<?php

namespace webignition\BasilParser\Provider\Page;

use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Model\Page\PageInterface;

interface PageProviderInterface
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
