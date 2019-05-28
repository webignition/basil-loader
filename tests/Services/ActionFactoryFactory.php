<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Factory\Action\ActionFactory;
use webignition\BasilParser\Factory\Action\InputActionTypeFactory;
use webignition\BasilParser\Factory\Action\InteractionActionTypeFactory;
use webignition\BasilParser\Factory\Action\NoArgumentsActionTypeFactory;
use webignition\BasilParser\Factory\Action\WaitActionTypeFactory;
use webignition\BasilParser\Factory\IdentifierFactory;
use webignition\BasilParser\Factory\ValueFactory;
use webignition\BasilParser\IdentifierStringExtractor\IdentifierStringExtractor;

class ActionFactoryFactory
{
    public static function create(): ActionFactory
    {
        $actionFactory = new ActionFactory();

        $identifierFactory = new IdentifierFactory();
        $identifierStringExtractor = new IdentifierStringExtractor();
        $valueFactory = new ValueFactory();

        $inputActionTypeFactory = new InputActionTypeFactory(
            $identifierFactory,
            $identifierStringExtractor,
            $valueFactory
        );

        $interactionActionTypeFactory = new InteractionActionTypeFactory($identifierFactory);

        $actionFactory->addActionTypeFactory($interactionActionTypeFactory);
        $actionFactory->addActionTypeFactory(new WaitActionTypeFactory());
        $actionFactory->addActionTypeFactory(new NoArgumentsActionTypeFactory());
        $actionFactory->addActionTypeFactory($inputActionTypeFactory);

        return $actionFactory;
    }
}
