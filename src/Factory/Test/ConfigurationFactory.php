<?php

namespace webignition\BasilParser\Factory\Test;

use webignition\BasilModel\PageUrlReference\PageUrlReference;
use webignition\BasilModel\Test\Configuration;
use webignition\BasilModel\Test\ConfigurationInterface;
use webignition\BasilParser\DataStructure\Test\Configuration as ConfigurationData;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Provider\Page\PageProviderInterface;

class ConfigurationFactory
{
    /**
     * @param ConfigurationData $configurationData
     * @param PageProviderInterface $pageProvider
     *
     * @return ConfigurationInterface
     *
     * @throws NonRetrievablePageException
     * @throws UnknownPageException
     * @throws MalformedPageElementReferenceException
     * @throws UnknownPageElementException
     */
    public function createFromConfigurationData(
        ConfigurationData $configurationData,
        PageProviderInterface $pageProvider
    ): ConfigurationInterface {
        $url = $configurationData->getUrl();

        $pageUrlReference = new PageUrlReference($url);
        if ($pageUrlReference->isValid()) {
            $pageImportName = $pageUrlReference->getImportName();

            $page = $pageProvider->findPage($pageImportName);
            $url = (string) $page->getUri();
        }

        return new Configuration($configurationData->getBrowser(), $url);
    }
}
