<?php

namespace webignition\BasilParser\Model;

class WaitAction extends Action implements ActionInterface, WaitActionInterface
{
    private $numberOfSeconds = 0;

    public function __construct(int $numberOfSeconds)
    {
        parent::__construct(ActionTypesInterface::WAIT);

        $this->numberOfSeconds = $numberOfSeconds;
    }

    public function getNumberOfSeconds(): int
    {
        return $this->numberOfSeconds;
    }
}
