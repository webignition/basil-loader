<?php

declare(strict_types=1);

namespace webignition\BasilLoader;

use webignition\BasilDataValidator\PageValidator;
use webignition\BasilLoader\Exception\InvalidPageException;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModels\Page\PageInterface;
use webignition\BasilParser\PageParser;
use webignition\BasilValidationResult\InvalidResultInterface;

class PageLoader
{
    private $yamlLoader;
    private $pageParser;
    private $pageValidator;

    public function __construct(YamlLoader $yamlLoader, PageParser $pageParser, PageValidator $pageValidator)
    {
        $this->yamlLoader = $yamlLoader;
        $this->pageParser = $pageParser;
        $this->pageValidator = $pageValidator;
    }

    public static function createLoader(): PageLoader
    {
        return new PageLoader(
            YamlLoader::createLoader(),
            PageParser::create(),
            PageValidator::create()
        );
    }

    /**
     * @param string $importName
     * @param string $path
     *
     * @return PageInterface
     *
     * @throws YamlLoaderException
     * @throws InvalidPageException
     */
    public function load(string $importName, string $path): PageInterface
    {
        $data = $this->yamlLoader->loadArray($path);

        $page = $this->pageParser->parse($importName, $data);

        $validationResult = $this->pageValidator->validate($page);
        if ($validationResult instanceof InvalidResultInterface) {
            throw new InvalidPageException($importName, $path, $validationResult);
        }

        return $page;
    }
}
