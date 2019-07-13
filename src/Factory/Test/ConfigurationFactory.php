<?php

namespace webignition\BasilParser\Factory\Test;

use webignition\BasilModel\Test\Configuration;
use webignition\BasilModel\Test\ConfigurationInterface;
use webignition\BasilParser\DataStructure\Test\Configuration as ConfigurationData;

class ConfigurationFactory
{
    /**
     * @param ConfigurationData $configurationData
     *
     * @return ConfigurationInterface
     */
    public function createFromConfigurationData(ConfigurationData $configurationData): ConfigurationInterface
    {
        return new Configuration($configurationData->getBrowser(), $configurationData->getUrl());
    }
}
