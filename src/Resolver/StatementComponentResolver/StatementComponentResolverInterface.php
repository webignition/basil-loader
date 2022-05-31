<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Resolver\StatementComponentResolver;

use webignition\BasilLoader\Resolver\ResolvedComponent;
use webignition\BasilLoader\Resolver\UnknownElementException;
use webignition\BasilLoader\Resolver\UnknownPageElementException;
use webignition\BasilModelProvider\Exception\UnknownItemException;
use webignition\BasilModelProvider\Identifier\IdentifierProviderInterface;
use webignition\BasilModelProvider\Page\PageProviderInterface;

interface StatementComponentResolverInterface
{
    /**
     * @throws UnknownElementException
     * @throws UnknownPageElementException
     * @throws UnknownItemException
     */
    public function resolve(
        ?string $data,
        PageProviderInterface $pageProvider,
        IdentifierProviderInterface $identifierProvider
    ): ?ResolvedComponent;
}
