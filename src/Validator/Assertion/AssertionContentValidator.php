<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Validator\Assertion;

use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilLoader\Validator\ResultInterface;
use webignition\BasilLoader\Validator\ValidResult;
use webignition\BasilLoader\Validator\ValueValidator;

class AssertionContentValidator
{
    private IdentifierTypeAnalyser $identifierTypeAnalyser;
    private ValueValidator $valueValidator;

    public function __construct(IdentifierTypeAnalyser $identifierTypeAnalyser, ValueValidator $valueValidator)
    {
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
        $this->valueValidator = $valueValidator;
    }

    public static function create(): AssertionContentValidator
    {
        return new AssertionContentValidator(
            IdentifierTypeAnalyser::create(),
            ValueValidator::create()
        );
    }

    public function validate(string $content): ResultInterface
    {
        if ($this->identifierTypeAnalyser->isDomOrDescendantDomIdentifier($content)) {
            return new ValidResult($content);
        }

        return $this->valueValidator->validate($content);
    }
}
