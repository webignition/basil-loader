<?php

namespace webignition\BasilParser\Loader;

use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilParser\Builder\StepBuilder;
use webignition\BasilDataStructure\Step as StepData;
use webignition\BasilParser\Exception\CircularStepImportException;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Exception\YamlLoaderException;
use webignition\BasilParser\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Step\StepProviderInterface;

class StepLoader
{
    private $yamlLoader;
    private $stepBuilder;

    public function __construct(YamlLoader $yamlLoader, StepBuilder $stepBuilder)
    {
        $this->yamlLoader = $yamlLoader;
        $this->stepBuilder = $stepBuilder;
    }

    public static function createLoader(): StepLoader
    {
        return new StepLoader(
            YamlLoader::createLoader(),
            StepBuilder::createBuilder()
        );
    }


    /**
     * @param string $path
     * @param StepProviderInterface $stepProvider
     * @param DataSetProviderInterface $dataSetProvider
     * @param PageProviderInterface $pageProvider
     *
     * @return StepInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableDataProviderException
     * @throws NonRetrievablePageException
     * @throws NonRetrievableStepException
     * @throws UnknownDataProviderException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws UnknownStepException
     * @throws YamlLoaderException
     * @throws CircularStepImportException
     */
    public function load(
        string $path,
        StepProviderInterface $stepProvider,
        DataSetProviderInterface $dataSetProvider,
        PageProviderInterface $pageProvider
    ): StepInterface {
        $data = $this->yamlLoader->loadArray($path);
        $stepData = new StepData($data);

        return $this->stepBuilder->build($stepData, $stepProvider, $dataSetProvider, $pageProvider);
    }
}
