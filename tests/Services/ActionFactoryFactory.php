<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Factory\Action\ActionFactory;
use webignition\BasilParser\Factory\Action\InputActionTypeFactory;
use webignition\BasilParser\Factory\Action\InteractionActionTypeFactory;
use webignition\BasilParser\Factory\Action\NoArgumentsActionTypeFactory;
use webignition\BasilParser\Factory\Action\WaitActionTypeFactory;

class ActionFactoryFactory
{
    public static function create(): ActionFactory
    {
        $actionFactory = new ActionFactory();

        $actionFactory->addActionTypeFactory(new InteractionActionTypeFactory());
        $actionFactory->addActionTypeFactory(new WaitActionTypeFactory());
        $actionFactory->addActionTypeFactory(new NoArgumentsActionTypeFactory());
        $actionFactory->addActionTypeFactory(new InputActionTypeFactory());

        return $actionFactory;
    }
}
