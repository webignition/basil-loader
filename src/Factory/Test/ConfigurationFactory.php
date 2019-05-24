<?php

namespace webignition\BasilParser\Factory\Test;

use webignition\BasilParser\Model\Test\Configuration;
use webignition\BasilParser\Model\Test\ConfigurationInterface;

class ConfigurationFactory
{
    const KEY_BROWSER = 'browser';
    const KEY_URL = 'url';

    public function createFromConfigurationData(array $configurationData): ConfigurationInterface
    {
        $browser = $configurationData[self::KEY_BROWSER] ?? '';
        $url = $configurationData[self::KEY_URL] ?? '';

        $browser = is_string($browser) ? $browser : '';
        $url = is_string($url) ? $url : '';

        return new Configuration($browser, $url);
    }
}
