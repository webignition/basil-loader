<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Resolver\StatementComponentResolver;

use webignition\BasilLoader\Resolver\ElementResolver;
use webignition\BasilLoader\Resolver\ResolvedComponent;
use webignition\BasilLoader\Resolver\UnknownElementException;
use webignition\BasilLoader\Resolver\UnknownPageElementException;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\BasilModels\Provider\Identifier\IdentifierProviderInterface;
use webignition\BasilModels\Provider\Page\PageProviderInterface;

class ComponentElementResolver implements StatementComponentResolverInterface
{
    public function __construct(
        private ElementResolver $elementResolver
    ) {
    }

    public static function createResolver(): self
    {
        return new ComponentElementResolver(
            ElementResolver::createResolver()
        );
    }

    /**
     * @throws UnknownElementException
     * @throws UnknownPageElementException
     * @throws UnknownItemException
     */
    public function resolve(
        ?string $data,
        PageProviderInterface $pageProvider,
        IdentifierProviderInterface $identifierProvider
    ): ?ResolvedComponent {
        if (is_string($data)) {
            return new ResolvedComponent(
                $data,
                $this->elementResolver->resolve($data, $pageProvider, $identifierProvider)
            );
        }

        return null;
    }
}
