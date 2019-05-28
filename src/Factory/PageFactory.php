<?php

namespace webignition\BasilParser\Factory;

use Nyholm\Psr7\Uri;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Model\Identifier\IdentifierInterface;
use webignition\BasilParser\Model\Page\Page;
use webignition\BasilParser\Model\Page\PageInterface;

class PageFactory
{
    const KEY_URL = 'url';
    const KEY_ELEMENTS = 'elements';

    /**
     * @var IdentifierFactory
     */
    private $identifierFactory;

    public function __construct(IdentifierFactory $identifierFactory)
    {
        $this->identifierFactory = $identifierFactory;
    }

    /**
     * @param array $pageData
     *
     * @return PageInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievablePageException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     */
    public function createFromPageData(array $pageData): PageInterface
    {
        $uriString = $pageData[self::KEY_URL] ?? '';
        $elementsData = $pageData[self::KEY_ELEMENTS] ?? [];
        $elementsData = is_array($elementsData) ? $elementsData : [];

        $uri = new Uri($uriString);

        $elementIdentifiers = [];

        foreach ($elementsData as $elementName => $identifierString) {
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
