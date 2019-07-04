<?php

namespace webignition\BasilParser\Builder;

use webignition\BasilModel\DataSet\DataSet;
use webignition\BasilModel\PageElementReference\PageElementReference;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilParser\DataStructure\Step as StepData;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Provider\Step\StepProviderInterface;

class StepBuilder
{
    private $stepFactory;

    public function __construct(StepFactory $stepFactory)
    {
        $this->stepFactory = $stepFactory;
    }

    /**
     * @param StepData $stepData
     * @param StepProviderInterface $stepProvider
     * @param DataSetProviderInterface $dataSetProvider
     *
     * @return StepInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableDataProviderException
     * @throws UnknownDataProviderException
     * @throws NonRetrievableStepException
     * @throws UnknownStepException
     */
    public function build(
        StepData $stepData,
        StepProviderInterface $stepProvider,
        DataSetProviderInterface $dataSetProvider
    ) {
        $stepImportName = $stepData->getImportName();

        $step = ('' === $stepImportName)
            ? $this->stepFactory->createFromStepData($stepData)
            : $stepProvider->findStep($stepImportName);

        $data = [];

        $dataProviderImportName = $stepData->getDataImportName();
        if ('' !== $dataProviderImportName) {
            $data = $dataSetProvider->findDataSetCollection($dataProviderImportName);
        }

        $dataArray = $stepData->getDataArray();
        if (!empty($dataArray)) {
            foreach ($dataArray as $key => $dataSetData) {
                $data[$key] = new DataSet($dataSetData);
            }
        }

        if (!empty($data)) {
            $step = $step->withDataSets($data);
        }

        $elementUses = $stepData->getElements();

        if (!empty($elementUses)) {
            $elementIdentifiers = [];

            foreach ($elementUses as $elementName => $pageModelElementReferenceString) {
                $pageElementReference = new PageElementReference($pageModelElementReferenceString);

                if (!$pageElementReference->isValid()) {
                    throw new MalformedPageElementReferenceException($pageElementReference);
                }
            }

            $step = $step->withElementIdentifiers($elementIdentifiers);
        }

        return $step;
    }
}
