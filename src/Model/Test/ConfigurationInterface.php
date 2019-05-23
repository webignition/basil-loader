<?php

namespace webignition\BasilParser\Model\Test;

interface ConfigurationInterface
{
    public function getBrowser(): string;
    public function getUrl(): string;
}
