<?php

namespace webignition\BasilParser\Provider\Page;

use webignition\BasilModel\Page\PageInterface;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageException;

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
     */
    public function findPage(string $importName): PageInterface;
}
