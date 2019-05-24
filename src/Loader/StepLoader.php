<?php

namespace webignition\BasilParser\Loader;

use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Model\Step\StepInterface;
use webignition\BasilParser\PageCollection\EmptyPageCollection;

class StepLoader
{
    private $yamlLoader;
    private $stepFactory;

    public function __construct(YamlLoader $yamlLoader, StepFactory $stepFactory)
    {
        $this->yamlLoader = $yamlLoader;
        $this->stepFactory = $stepFactory;
    }

    /**
     * @param string $path
     *
     * @return StepInterface
     *
     * @throws YamlLoaderException
     * @throws MalformedPageElementReferenceException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws NonRetrievablePageException
     */
    public function load(string $path): StepInterface
    {
        $data = $this->yamlLoader->loadArray($path);

        return $this->stepFactory->createFromStepData($data, new EmptyPageCollection());
    }
}
