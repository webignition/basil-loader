<?php

namespace webignition\BasilParser\Factory;

use Nyholm\Psr7\Uri;
use webignition\BasilParser\DataStructure\Page as PageData;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Model\Identifier\IdentifierInterface;
use webignition\BasilParser\Model\Page\Page;
use webignition\BasilParser\Model\Page\PageInterface;

class PageFactory
{
    /**
     * @var IdentifierFactory
     */
    private $identifierFactory;

    public function __construct(IdentifierFactory $identifierFactory)
    {
        $this->identifierFactory = $identifierFactory;
    }

    /**
     * @param PageData $pageData
     *
     * @return PageInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievablePageException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     */
    public function createFromPageData(PageData $pageData): PageInterface
    {
        $uriString = $pageData->getUrlString();
        $elementData = $pageData->getElementData();

        $uri = new Uri($uriString);

        $elementIdentifiers = [];

        foreach ($elementData as $elementName => $identifierString) {
            $identifier = $this->identifierFactory->createWithElementReference(
                $identifierString,
                $elementName,
                $elementIdentifiers
            );

            if ($identifier instanceof IdentifierInterface) {
                $elementIdentifiers[$elementName] = $identifier;
            }
        }

        return new Page($uri, $elementIdentifiers);
    }
}
