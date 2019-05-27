<?php

namespace webignition\BasilParser\Factory\Test;

use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Model\PageUrlReference\PageUrlReference;
use webignition\BasilParser\Model\Test\Configuration;
use webignition\BasilParser\Model\Test\ConfigurationInterface;
use webignition\BasilParser\PageProvider\PageProviderInterface;

class ConfigurationFactory
{
    const KEY_BROWSER = 'browser';
    const KEY_URL = 'url';

    /**
     * @param array $configurationData
     * @param PageProviderInterface $pageProvider
     *
     * @return ConfigurationInterface
     *
     * @throws NonRetrievablePageException
     * @throws UnknownPageException
     */
    public function createFromConfigurationData(
        array $configurationData,
        PageProviderInterface $pageProvider
    ): ConfigurationInterface {
        $browser = $configurationData[self::KEY_BROWSER] ?? '';
        $url = $configurationData[self::KEY_URL] ?? '';

        $browser = is_string($browser) ? $browser : '';
        $url = is_string($url) ? $url : '';

        $pageUrlReference = new PageUrlReference($url);
        if ($pageUrlReference->isValid()) {
            $pageImportName = $pageUrlReference->getImportName();

            $page = $pageProvider->findPage($pageImportName);
            $url = (string) $page->getUri();
        }

        return new Configuration($browser, $url);
    }
}
