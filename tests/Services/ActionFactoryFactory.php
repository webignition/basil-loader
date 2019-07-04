<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Factory\Action\ActionFactory;
use webignition\BasilParser\Factory\Action\InputActionTypeFactory;
use webignition\BasilParser\Factory\Action\InteractionActionTypeFactory;
use webignition\BasilParser\Factory\Action\NoArgumentsActionTypeFactory;
use webignition\BasilParser\Factory\Action\WaitActionTypeFactory;
use webignition\BasilParser\Factory\ValueFactory;

class ActionFactoryFactory
{
    public static function create(): ActionFactory
    {
        $actionFactory = new ActionFactory();

        $identifierFactory = IdentifierFactoryFactory::create();
        $valueFactory = new ValueFactory();

        $inputActionTypeFactory = new InputActionTypeFactory(
            $identifierFactory,
            IdentifierStringExtractorFactory::create(),
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
