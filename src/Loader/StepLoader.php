<?php

namespace webignition\BasilParser\Loader;

use webignition\BasilParser\DataStructure\Step as StepData;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Exception\YamlLoaderException;
use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Model\Step\StepInterface;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;

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
        $stepData = new StepData($data);

        return $this->stepFactory->createFromStepData($stepData, new EmptyPageProvider());
    }
}
