<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Factory\IdentifierFactory;
use webignition\BasilParser\Factory\ValueFactory;
use webignition\BasilParser\IdentifierStringExtractor\IdentifierStringExtractor;
use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InputAction;

class InputActionFactory extends AbstractActionFactory implements ActionFactoryInterface
{
    const IDENTIFIER_STOP_WORD = ' to ';

    private $identifierFactory;
    private $identifierStringExtractor;
    private $valueFactory;

    public function __construct()
    {
        $this->identifierFactory = new IdentifierFactory();
        $this->identifierStringExtractor = new IdentifierStringExtractor();
        $this->valueFactory = new ValueFactory();
    }

    protected function getHandledActionTypes(): array
    {
        return [
            ActionTypes::SET,
        ];
    }

    protected function doCreateFromTypeAndArguments(string $type, string $arguments): ActionInterface
    {
        $identifierString = $this->identifierStringExtractor->extractFromStart($arguments);

        if ('' === $identifierString) {
            return new InputAction(
                $this->identifierFactory->create(''),
                null,
                $arguments
            );
        }

        $trimmedStopWord = trim(self::IDENTIFIER_STOP_WORD);
        $endsWithStopStringRegex = '/(( ' . $trimmedStopWord . ' )|( ' . $trimmedStopWord . '))$/';

        if (preg_match($endsWithStopStringRegex, $arguments) > 0) {
            return new InputAction(
                $this->identifierFactory->create($identifierString),
                null,
                $arguments
            );
        }

        if ($arguments === $identifierString) {
            return new InputAction(
                $this->identifierFactory->create($identifierString),
                null,
                $arguments
            );
        }

        $keywordAndValueString = mb_substr($arguments, mb_strlen($identifierString));

        $hasToKeyword = substr(
            $keywordAndValueString,
            0,
            strlen(self::IDENTIFIER_STOP_WORD)
        ) === self::IDENTIFIER_STOP_WORD;

        if (!$hasToKeyword) {
            $keywordAndValueString = trim($keywordAndValueString);

            $value = '' === $keywordAndValueString
                ? null
                : $this->valueFactory->createFromValueString($keywordAndValueString);

            return new InputAction(
                $this->identifierFactory->create($identifierString),
                $value,
                $arguments
            );
        }

        $valueString = mb_substr($keywordAndValueString, mb_strlen(self::IDENTIFIER_STOP_WORD));
        $value = $this->valueFactory->createFromValueString($valueString);
        $identifier = $this->identifierFactory->create($identifierString);

        return new InputAction($identifier, $value, $arguments);
    }
}
