<?php

declare(strict_types=1);

namespace webignition\BasilLoader;

use webignition\BasilLoader\Exception\InvalidPageException;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilLoader\Validator\InvalidResult;
use webignition\BasilLoader\Validator\InvalidResultInterface;
use webignition\BasilLoader\Validator\PageValidator;
use webignition\BasilLoader\Validator\ResultType;
use webignition\BasilModels\Model\Page\PageInterface;
use webignition\BasilModels\Parser\Exception\InvalidPageException as InvalidPageModelException;
use webignition\BasilModels\Parser\PageParser;

class PageLoader
{
    public function __construct(
        private readonly YamlLoader $yamlLoader,
        private readonly PageParser $pageParser,
        private readonly PageValidator $pageValidator
    ) {}

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

        try {
            $page = $this->pageParser->parse($importName, $data);
        } catch (InvalidPageModelException) {
            $invalidResult = new InvalidResult(
                [
                    'import_name' => $importName,
                    'path' => $path,
                    'data' => $data,
                ],
                ResultType::PAGE,
                PageValidator::REASON_URL_EMPTY
            );

            throw new InvalidPageException($importName, $path, $invalidResult);
        }

        $validationResult = $this->pageValidator->validate($page);
        if ($validationResult instanceof InvalidResultInterface) {
            throw new InvalidPageException($importName, $path, $validationResult);
        }

        return $page;
    }
}
