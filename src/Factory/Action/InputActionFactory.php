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

    public function __construct(
        ?IdentifierFactory $identifierFactory = null,
        ?IdentifierStringExtractor $identifierStringExtractor = null,
        ?ValueFactory $valueFactory = null
    ) {
        $identifierFactory = $identifierFactory ?? new IdentifierFactory();
        $identifierStringExtractor = $identifierStringExtractor ?? new IdentifierStringExtractor();
        $valueFactory = $valueFactory ?? new ValueFactory();

        $this->identifierFactory = $identifierFactory;
        $this->identifierStringExtractor = $identifierStringExtractor;
        $this->valueFactory = $valueFactory;
    }

    protected function getHandledActionTypes(): array
    {
        return [
            ActionTypes::SET,
        ];
    }

    protected function doCreateFromTypeAndArguments(string $type, string $arguments): ActionInterface
    {
        $identifierString = $this->identifierStringExtractor->extractFromStart(
            $arguments,
            [self::IDENTIFIER_STOP_WORD]
        );

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

        $valueString = mb_substr($arguments, mb_strlen($identifierString . self::IDENTIFIER_STOP_WORD));

        if ($this->lacksToKeyword($arguments, $identifierString, $valueString)) {
            return new InputAction(
                $this->identifierFactory->create($identifierString),
                null,
                $arguments
            );
        }

        $valueString = mb_substr($arguments, mb_strlen($identifierString . self::IDENTIFIER_STOP_WORD));
        $value = $this->valueFactory->createFromValueString($valueString);
        $identifier = $this->identifierFactory->create($identifierString);

        return new InputAction($identifier, $value, $arguments);
    }

    private function lacksToKeyword(string $arguments, string $identifierString, string $valueString)
    {
        $valuePosition = mb_strpos($arguments, $valueString);
        $expectedValuePosition = mb_strlen($identifierString) + strlen(self::IDENTIFIER_STOP_WORD);

        return $valuePosition !== $expectedValuePosition;
    }
}
