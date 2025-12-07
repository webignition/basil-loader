<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit\Validator\Assertion;

use PHPUnit\Framework\TestCase;
use webignition\BasilLoader\Validator\Assertion\AssertionValidator;
use webignition\BasilLoader\Validator\InvalidResult;
use webignition\BasilLoader\Validator\InvalidResultInterface;
use webignition\BasilLoader\Validator\ResultType;
use webignition\BasilLoader\Validator\ValidResult;
use webignition\BasilLoader\Validator\ValueValidator;
use webignition\BasilModels\Model\Assertion\Assertion;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Parser\AssertionParser;

class AssertionValidatorTest extends TestCase
{
    private AssertionValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = AssertionValidator::create();
    }

    /**
     * @dataProvider invalidAssertionDataProvider
     */
    public function testValidateNotValid(AssertionInterface $assertion, InvalidResultInterface $expectedResult): void
    {
        $this->assertEquals($expectedResult, $this->validator->validate($assertion));
    }

    /**
     * @return array<mixed>
     */
    public static function invalidAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'invalid identifier' => [
                'assertion' => $assertionParser->parse('$elements.element_name is "value"'),
                'expectedResult' => new InvalidResult(
                    $assertionParser->parse('$elements.element_name is "value"'),
                    ResultType::ASSERTION,
                    AssertionValidator::REASON_INVALID_IDENTIFIER,
                    new InvalidResult(
                        '$elements.element_name',
                        ResultType::VALUE,
                        ValueValidator::REASON_INVALID
                    )
                ),
            ],
            'invalid operator' => [
                'assertion' => new Assertion('$".button" glows', '$".button"', 'glows'),
                'expectedResult' => (new InvalidResult(
                    new Assertion('$".button" glows', '$".button"', 'glows'),
                    ResultType::ASSERTION,
                    AssertionValidator::REASON_INVALID_OPERATOR
                ))->withContext([
                    AssertionValidator::CONTEXT_OPERATOR => 'glows',
                ]),
            ],
            'invalid value' => [
                'assertion' => $assertionParser->parse('$".selector" is $elements.element_name'),
                'expectedResult' => new InvalidResult(
                    $assertionParser->parse('$".selector" is $elements.element_name'),
                    ResultType::ASSERTION,
                    AssertionValidator::REASON_INVALID_VALUE,
                    new InvalidResult(
                        '$elements.element_name',
                        ResultType::VALUE,
                        ValueValidator::REASON_INVALID
                    )
                ),
            ],
        ];
    }

    /**
     * @dataProvider validAssertionIdentifierDataProvider
     * @dataProvider validAssertionOperatorDataProvider
     * @dataProvider validAssertionValueDataProvider
     */
    public function testValidateIsValid(AssertionInterface $assertion): void
    {
        $expectedResult = new ValidResult($assertion);

        $this->assertEquals($expectedResult, $this->validator->validate($assertion));
    }

    /**
     * @return array<mixed>
     */
    public static function validAssertionIdentifierDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'identifier: element identifier' => [
                'assertion' => $assertionParser->parse('$".selector" is "value"'),
            ],
            'identifier: descendant element identifier' => [
                'assertion' => $assertionParser->parse('$".parent" >> $".child" is "value"'),
            ],
            'identifier: attribute identifier' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name is "value"'),
            ],
            'identifier: quoted literal' => [
                'assertion' => $assertionParser->parse('"value" is "value"'),
            ],
            'identifier: browser property' => [
                'assertion' => $assertionParser->parse('$browser.size is "value"'),
            ],
            'identifier: page property' => [
                'assertion' => $assertionParser->parse('$page.title is "value"'),
            ],
            'identifier: data parameter' => [
                'assertion' => $assertionParser->parse('$data.key is "value"'),
            ],
            'identifier: environment parameter' => [
                'assertion' => $assertionParser->parse('$env.KEY is "value"'),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function validAssertionOperatorDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'operator: is' => [
                'assertion' => $assertionParser->parse('$".selector" is "value"'),
            ],
            'operator: is-not' => [
                'assertion' => $assertionParser->parse('$".selector" is-not "value"'),
            ],
            'operator: exists' => [
                'assertion' => $assertionParser->parse('$".selector" exists'),
            ],
            'operator: not-exists' => [
                'assertion' => $assertionParser->parse('$".selector" not-exists'),
            ],
            'operator: includes' => [
                'assertion' => $assertionParser->parse('$".selector" includes "value"'),
            ],
            'operator: excludes' => [
                'assertion' => $assertionParser->parse('$".selector" excludes "value"'),
            ],
            'operator: matches' => [
                'assertion' => $assertionParser->parse('$".selector" matches "value"'),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public static function validAssertionValueDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'value: element identifier' => [
                'assertion' => $assertionParser->parse('"value" is $".selector"'),
            ],
            'value: descendant element identifier' => [
                'assertion' => $assertionParser->parse('"value" is $".parent" >> $".child"'),
            ],
            'value: attribute identifier' => [
                'assertion' => $assertionParser->parse('"value" is $".selector".attribute_name'),
            ],
            'value: quoted literal' => [
                'assertion' => $assertionParser->parse('"value" is "value"'),
            ],
            'value: browser property' => [
                'assertion' => $assertionParser->parse('"value" is $browser.size'),
            ],
            'value: page property' => [
                'assertion' => $assertionParser->parse('"value" is $page.title'),
            ],
            'value: data parameter' => [
                'assertion' => $assertionParser->parse('"value" is $data.key'),
            ],
            'value: environment parameter' => [
                'assertion' => $assertionParser->parse('"value" is $env.KEY'),
            ],
        ];
    }
}
