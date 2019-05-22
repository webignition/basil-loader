<?php

namespace webignition\BasilParser\Factory;

use Nyholm\Psr7\Uri;
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

    public function __construct()
    {
        $this->identifierFactory = new IdentifierFactory();
    }

    public function createFromPageData(array $pageData): PageInterface
    {
        $uriString = $pageData[self::KEY_URL] ?? '';
        $elementsData = $pageData[self::KEY_ELEMENTS] ?? [];

        $uri = new Uri($uriString);
        $elementIdentifiers = [];

        foreach ($elementsData as $elementName => $identifierString) {
            $identifier = $this->identifierFactory->createWithElementReference($identifierString);

            $elementIdentifiers[$elementName] = $identifier;
        }

        return new Page($uri, $elementIdentifiers);
    }
}
