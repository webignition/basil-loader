<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Validator;

use webignition\BasilModels\Model\Page\PageInterface;
use webignition\BasilValueExtractor\DescendantIdentifierExtractor;
use webignition\BasilValueExtractor\ElementIdentifierExtractor;

class PageValidator
{
    public const REASON_URL_EMPTY = 'page-url-empty';
    public const REASON_IDENTIFIER_INVALID = 'page-invalid-identifier';
    public const CONTEXT_NAME = 'name';
    public const CONTEXT_IDENTIFIER = 'identifier';

    private ElementIdentifierExtractor $elementIdentifierExtractor;
    private DescendantIdentifierExtractor $descendantIdentifierExtractor;

    public function __construct(
        ElementIdentifierExtractor $elementIdentifierExtractor,
        DescendantIdentifierExtractor $descendantIdentifierExtractor
    ) {
        $this->elementIdentifierExtractor = $elementIdentifierExtractor;
        $this->descendantIdentifierExtractor = $descendantIdentifierExtractor;
    }

    public static function create(): PageValidator
    {
        return new PageValidator(
            ElementIdentifierExtractor::createExtractor(),
            DescendantIdentifierExtractor::createExtractor()
        );
    }

    public function validate(PageInterface $page): ResultInterface
    {
        $identifiers = $page->getIdentifiers();

        foreach ($identifiers as $name => $identifier) {
            $descendantIdentifier = $this->descendantIdentifierExtractor->extractIdentifier($identifier);
            $elementIdentifier = $this->elementIdentifierExtractor->extract($identifier);

            $isDescendantDomIdentifier = null !== $descendantIdentifier;
            $isElementIdentifier =
                false === $isDescendantDomIdentifier
                && null !== $elementIdentifier;
            $isAttributeIdentifier =
                $isElementIdentifier && $this->isAttributeIdentifierMatch((string) $elementIdentifier);
            $isElementIdentifier = $isElementIdentifier && !$isAttributeIdentifier;

            if (!$isElementIdentifier && !$isDescendantDomIdentifier) {
                return (new InvalidResult(
                    $page,
                    ResultType::PAGE,
                    self::REASON_IDENTIFIER_INVALID
                ))->withContext([
                    self::CONTEXT_NAME => $name,
                    self::CONTEXT_IDENTIFIER => $identifier,
                ]);
            }
        }

        return new ValidResult($page);
    }

    private function isAttributeIdentifierMatch(string $elementIdentifier): bool
    {
        if ('' === $elementIdentifier) {
            return false;
        }

        if ('"' === mb_substr($elementIdentifier, -1)) {
            return false;
        }

        if (preg_match('/:[0-9]+$/', $elementIdentifier)) {
            return false;
        }

        return preg_match('/\.(.+)$/', $elementIdentifier) > 0;
    }
}
