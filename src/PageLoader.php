<?php

declare(strict_types=1);

namespace webignition\BasilLoader;

use webignition\BasilLoader\Exception\InvalidPageException;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilLoader\Validator\InvalidResultInterface;
use webignition\BasilLoader\Validator\PageValidator;
use webignition\BasilModels\Page\PageInterface;
use webignition\BasilParser\PageParser;

class PageLoader
{
    public function __construct(
        private YamlLoader $yamlLoader,
        private PageParser $pageParser,
        private PageValidator $pageValidator
    ) {
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
