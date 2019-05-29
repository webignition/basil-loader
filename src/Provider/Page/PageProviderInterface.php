<?php

namespace webignition\BasilParser\Provider\Page;

use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Model\Page\PageInterface;

interface PageProviderInterface
{
    /**
     * @param string $importName
     *
     * @return PageInterface
     *
     * @throws NonRetrievablePageException
     * @throws UnknownPageException
     * @throws MalformedPageElementReferenceException
     * @throws UnknownPageElementException
     */
    public function findPage(string $importName): PageInterface;
}
