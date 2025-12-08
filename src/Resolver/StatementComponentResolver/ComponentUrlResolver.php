<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Resolver\StatementComponentResolver;

use webignition\BasilLoader\Resolver\ImportedUrlResolver;
use webignition\BasilLoader\Resolver\ResolvedComponent;
use webignition\BasilModels\Model\PageProperty\PageProperty;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\BasilModels\Provider\ProviderInterface;

class ComponentUrlResolver implements StatementComponentResolverInterface
{
    public function __construct(
        private ImportedUrlResolver $importedUrlResolver
    ) {}

    public static function createResolver(): self
    {
        return new ComponentUrlResolver(
            ImportedUrlResolver::createResolver()
        );
    }

    /**
     * @throws UnknownItemException
     */
    public function resolve(
        ?string $data,
        ProviderInterface $pageProvider,
        ProviderInterface $identifierProvider
    ): ?ResolvedComponent {
        if (!is_string($data)) {
            return null;
        }

        if (PageProperty::is($data)) {
            return null;
        }

        $url = trim($data);
        if ('' === $url) {
            return null;
        }

        $resolvedData = $this->importedUrlResolver->resolve($url, $pageProvider);
        if ($data !== $resolvedData) {
            $resolvedData = '"' . $resolvedData . '"';
        }

        return new ResolvedComponent($data, $resolvedData);
    }
}
