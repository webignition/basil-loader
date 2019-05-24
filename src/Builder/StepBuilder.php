<?php

namespace webignition\BasilParser\Builder;

use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Loader\DataSetLoader;
use webignition\BasilParser\Loader\StepLoader;
use webignition\BasilParser\Loader\YamlLoaderException;
use webignition\BasilParser\Model\PageElementReference\PageElementReference;
use webignition\BasilParser\Model\Step\StepInterface;
use webignition\BasilParser\PageCollection\PageCollectionInterface;

class StepBuilder
{
    const KEY_USE = 'use';
    const KEY_DATA = 'data';
    const KEY_ELEMENTS = 'elements';

    private $stepFactory;
    private $stepLoader;
    private $dataSetLoader;

    public function __construct(StepFactory $stepFactory, StepLoader $stepLoader, DataSetLoader $dataSetLoader)
    {
        $this->stepFactory = $stepFactory;
        $this->stepLoader = $stepLoader;
        $this->dataSetLoader = $dataSetLoader;
    }

    /**
     * @param string $stepName
     * @param array $stepData
     * @param string[] $stepImportPaths
     * @param string[] $dataProviderImportPaths
     * @param PageCollectionInterface $pages
     *
     * @return StepInterface
     *
     * @throws StepBuilderInvalidPageElementReferenceException
     * @throws StepBuilderUnknownDataProviderImportException
     * @throws StepBuilderUnknownPageElementException
     * @throws StepBuilderUnknownStepImportException
     * @throws YamlLoaderException
     * @throws MalformedPageElementReferenceException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws NonRetrievablePageException
     */
    public function build(
        string $stepName,
        array $stepData,
        array $stepImportPaths,
        array $dataProviderImportPaths,
        PageCollectionInterface $pages
    ) {
        $stepImportName = $stepData[self::KEY_USE] ?? null;
        if (null === $stepImportName) {
            $step = $this->stepFactory->createFromStepData($stepData, $pages);
        } else {
            $stepImportPath = $stepImportPaths[$stepImportName] ?? null;

            if (null === $stepImportPath) {
                throw new StepBuilderUnknownStepImportException($stepName, $stepImportName, $stepImportPaths);
            }

            $step = $this->stepLoader->load($stepImportPath);
        }

        $data = $stepData[self::KEY_DATA] ?? null;
        if (null !== $data) {
            if (is_string($data)) {
                $dataProviderImportName = $data;
                $dataProviderImportPath = $dataProviderImportPaths[$dataProviderImportName] ?? null;

                if (null === $dataProviderImportPath) {
                    throw new StepBuilderUnknownDataProviderImportException(
                        $stepName,
                        $dataProviderImportName,
                        $dataProviderImportPaths
                    );
                }

                $data = $this->dataSetLoader->load($dataProviderImportPath);
            }

            if (is_array($data)) {
                $step = $step->withDataSets($data);
            }
        }

        $elementUses = $stepData[self::KEY_ELEMENTS] ?? null;

        if (null !== $elementUses) {
            $elementIdentifiers = [];

            foreach ($elementUses as $elementName => $pageModelElementReferenceString) {
                $pageModelElementReference = new PageElementReference($pageModelElementReferenceString);

                if (!$pageModelElementReference->isValid()) {
                    throw new StepBuilderInvalidPageElementReferenceException(
                        $stepName,
                        $pageModelElementReferenceString
                    );
                }

                $pageImportName = $pageModelElementReference->getImportName();
                $elementName = $pageModelElementReference->getElementName();

                $page = $pages->findPage($pageImportName);

                $elementIdentifier = $page->getElementIdentifier($elementName);

                if (null === $elementIdentifier) {
                    throw new StepBuilderUnknownPageElementException(
                        $stepName,
                        $pageImportName,
                        $elementName,
                        $page->getElementNames()
                    );
                }

                $elementIdentifiers[$elementName] = $elementIdentifier;
            }

            $step = $step->withElementIdentifiers($elementIdentifiers);
        }

        return $step;
    }
}
