<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilModel\Identifier\Identifier;
use webignition\BasilModel\Identifier\IdentifierInterface;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ValueTypes;

class TestIdentifierFactory
{
    public static function createElementIdentifier(
        string $type,
        string $selector,
        int $position = null,
        ?string $name = null,
        ?ElementIdentifierInterface $parentIdentifier = null
    ): ElementIdentifierInterface {
        $value = $type === ValueTypes::CSS_SELECTOR
            ? LiteralValue::createCssSelectorValue($selector)
            : LiteralValue::createXpathExpressionValue($selector);

        $identifier = new ElementIdentifier($value, $position);

        if (null !== $name) {
            $identifier = $identifier->withName($name);
        }

        if ($identifier instanceof ElementIdentifierInterface &&
            $parentIdentifier instanceof ElementIdentifierInterface) {
            $identifier = $identifier->withParentIdentifier($parentIdentifier);
        }

        if ($identifier instanceof ElementIdentifierInterface) {
            return $identifier;
        }

        throw new \RuntimeException('Identifier is not an ElementIdentifierInterface instance');
    }

    public static function createCssElementIdentifier(
        string $selector,
        int $position = null,
        ?string $name = null,
        ?ElementIdentifierInterface $parentIdentifier = null
    ): ElementIdentifierInterface {
        return self::createElementIdentifier(ValueTypes::CSS_SELECTOR, $selector, $position, $name, $parentIdentifier);
    }

    public static function createXpathElementIdentifier(
        string $selector,
        int $position = null,
        ?string $name = null,
        ?ElementIdentifierInterface $parentIdentifier = null
    ): ElementIdentifierInterface {
        return self::createElementIdentifier(
            ValueTypes::XPATH_EXPRESSION,
            $selector,
            $position,
            $name,
            $parentIdentifier
        );
    }

    public static function createPageElementReferenceIdentifier(
        ObjectValueInterface $value,
        ?string $name = null
    ): IdentifierInterface {
        $identifier = new Identifier(
            IdentifierTypes::PAGE_ELEMENT_REFERENCE,
            $value
        );

        if (null !== $name) {
            $identifier = $identifier->withName($name);
        }

        if ($identifier instanceof IdentifierInterface) {
            return $identifier;
        }

        throw new \RuntimeException('Identifier is not an IdentifierInterface instance');
    }
}
