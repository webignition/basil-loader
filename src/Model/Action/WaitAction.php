<?php

namespace webignition\BasilParser\Model\Action;

class WaitAction extends Action implements ActionInterface, WaitActionInterface
{
    private $duration;

    public function __construct(string $duration)
    {
        parent::__construct(ActionTypes::WAIT);

        $this->duration = $duration;
    }

    public function getDuration(): string
    {
        return $this->duration;
    }
}
