<?php

namespace webignition\BasilParser\Builder;

use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Model\PageElementReference\PageElementReference;
use webignition\BasilParser\Model\Step\StepInterface;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Step\StepProviderInterface;

class StepBuilder
{
    const KEY_USE = 'use';
    const KEY_DATA = 'data';
    const KEY_ELEMENTS = 'elements';

    private $stepFactory;

    public function __construct(StepFactory $stepFactory)
    {
        $this->stepFactory = $stepFactory;
    }

    /**
     * @param array $stepData
     * @param StepProviderInterface $stepProvider
     * @param DataSetProviderInterface $dataSetProvider
     * @param PageProviderInterface $pageProvider
     *
     * @return StepInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableDataProviderException
     * @throws NonRetrievablePageException
     * @throws UnknownDataProviderException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws NonRetrievableStepException
     * @throws UnknownStepException
     */
    public function build(
        array $stepData,
        StepProviderInterface $stepProvider,
        DataSetProviderInterface $dataSetProvider,
        PageProviderInterface $pageProvider
    ) {
        $stepImportName = $stepData[self::KEY_USE] ?? null;
        $step = null === $stepImportName
            ? $this->stepFactory->createFromStepData($stepData, $pageProvider)
            : $stepProvider->findStep($stepImportName);

        $data = $stepData[self::KEY_DATA] ?? null;
        if (null !== $data) {
            if (is_string($data)) {
                $dataProviderImportName = $data;
                $data = $dataSetProvider->findDataSetCollection($dataProviderImportName);
            }

            if (is_array($data)) {
                $step = $step->withDataSets($data);
            }
        }

        $elementUses = $stepData[self::KEY_ELEMENTS] ?? null;

        if (null !== $elementUses) {
            $elementIdentifiers = [];

            foreach ($elementUses as $elementName => $pageModelElementReferenceString) {
                $pageElementReference = new PageElementReference($pageModelElementReferenceString);

                if (!$pageElementReference->isValid()) {
                    throw new MalformedPageElementReferenceException($pageElementReference);
                }

                $pageImportName = $pageElementReference->getImportName();
                $elementName = $pageElementReference->getElementName();

                $page = $pageProvider->findPage($pageImportName);

                $elementIdentifier = $page->getElementIdentifier($elementName);

                if (null === $elementIdentifier) {
                    throw new UnknownPageElementException($pageImportName, $elementName);
                }

                $elementIdentifiers[$elementName] = $elementIdentifier;
            }

            $step = $step->withElementIdentifiers($elementIdentifiers);
        }

        return $step;
    }
}
