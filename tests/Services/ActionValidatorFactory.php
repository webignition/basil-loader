<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Validator\Action\ActionValidator;
use webignition\BasilParser\Validator\Action\InputActionValidator;
use webignition\BasilParser\Validator\Action\InteractionActionValidator;
use webignition\BasilParser\Validator\Action\NoArgumentsActionValidator;
use webignition\BasilParser\Validator\Action\WaitActionValidator;

class ActionValidatorFactory
{
    public static function create(): ActionValidator
    {
        $actionValidator = new ActionValidator();

        $actionValidator->addActionTypeValidator(new InputActionValidator());
        $actionValidator->addActionTypeValidator(new InteractionActionValidator());
        $actionValidator->addActionTypeValidator(new NoArgumentsActionValidator());
        $actionValidator->addActionTypeValidator(new WaitActionValidator());

        return $actionValidator;
    }
}
