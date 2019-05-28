<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Factory\IdentifierFactory;
use webignition\BasilParser\Factory\ValueFactory;
use webignition\BasilParser\IdentifierStringExtractor\IdentifierStringExtractor;
use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InputAction;
use webignition\BasilParser\PageProvider\PageProviderInterface;

class InputActionTypeFactory extends AbstractActionTypeFactory implements ActionTypeFactoryInterface
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

    /**
     * @param string $type
     * @param string $arguments
     * @param PageProviderInterface $pageProvider
     *
     * @return ActionInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws NonRetrievablePageException
     */
    protected function doCreateForActionType(
        string $type,
        string $arguments,
        PageProviderInterface $pageProvider
    ): ActionInterface {
        $identifierString = $this->identifierStringExtractor->extractFromStart($arguments);

        if ('' === $identifierString) {
            return new InputAction(null, null, $arguments);
        }

        $identifier = $this->identifierFactory->create($identifierString, $pageProvider);

        $trimmedStopWord = trim(self::IDENTIFIER_STOP_WORD);
        $endsWithStopStringRegex = '/(( ' . $trimmedStopWord . ' )|( ' . $trimmedStopWord . '))$/';

        if (preg_match($endsWithStopStringRegex, $arguments) > 0) {
            return new InputAction($identifier, null, $arguments);
        }

        if ($arguments === $identifierString) {
            return new InputAction($identifier, null, $arguments);
        }

        $keywordAndValueString = mb_substr($arguments, mb_strlen($identifierString));

        $stopWord = self::IDENTIFIER_STOP_WORD;
        $hasToKeyword = substr($keywordAndValueString, 0, strlen($stopWord)) === $stopWord;

        if ($hasToKeyword) {
            $valueString = mb_substr($keywordAndValueString, mb_strlen(self::IDENTIFIER_STOP_WORD));
            $value = $this->valueFactory->createFromValueString($valueString);
        } else {
            $value = '' === trim($keywordAndValueString)
                ? null
                : $this->valueFactory->createFromValueString($keywordAndValueString);
        }

        return new InputAction($identifier, $value, $arguments);
    }
}
