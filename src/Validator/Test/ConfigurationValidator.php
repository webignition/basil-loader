<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Validator\Test;

use webignition\BasilLoader\Validator\InvalidResult;
use webignition\BasilLoader\Validator\ResultInterface;
use webignition\BasilLoader\Validator\ResultType;
use webignition\BasilLoader\Validator\ValidResult;
use webignition\BasilModels\Model\PageUrlReference\PageUrlReference;
use webignition\BasilModels\Model\Test\Configuration;
use webignition\BasilModels\Model\Test\ConfigurationInterface;

class ConfigurationValidator
{
    public const REASON_BROWSER_EMPTY = 'test-configuration-browser-empty';
    public const REASON_URL_EMPTY = 'test-configuration-url-empty';
    public const REASON_URL_IS_PAGE_URL_REFERENCE = 'test-configuration-url-is-page-url-reference';

    public static function create(): ConfigurationValidator
    {
        return new ConfigurationValidator();
    }

    public function validate(ConfigurationInterface $configuration): ResultInterface
    {
        $validationState = $configuration->validate();

        if (Configuration::VALIDATION_STATE_BROWSER_EMPTY === $validationState) {
            return new InvalidResult(
                $configuration,
                ResultType::TEST_CONFIGURATION,
                self::REASON_BROWSER_EMPTY
            );
        }

        if (Configuration::VALIDATION_STATE_URL_EMPTY === $validationState) {
            return new InvalidResult(
                $configuration,
                ResultType::TEST_CONFIGURATION,
                self::REASON_URL_EMPTY
            );
        }

        $pageUrlReference = new PageUrlReference($configuration->getUrl());
        if ($pageUrlReference->isValid()) {
            return new InvalidResult(
                $configuration,
                ResultType::TEST_CONFIGURATION,
                self::REASON_URL_IS_PAGE_URL_REFERENCE
            );
        }

        return new ValidResult($configuration);
    }
}
