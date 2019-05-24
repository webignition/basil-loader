<?php

namespace webignition\BasilParser\Builder;

use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Loader\StepLoader;
use webignition\BasilParser\Loader\YamlLoader;
use webignition\BasilParser\Loader\YamlLoaderException;
use webignition\BasilParser\Model\Page\PageInterface;
use webignition\BasilParser\Model\PageElementReference\PageElementReference;
use webignition\BasilParser\Model\Step\StepInterface;

class StepBuilder
{
    const KEY_USE = 'use';
    const KEY_DATA = 'data';
    const KEY_ELEMENTS = 'elements';

    private $stepFactory;
    private $stepLoader;
    private $yamlLoader;

    public function __construct(StepFactory $stepFactory, StepLoader $stepLoader, YamlLoader $yamlLoader)
    {
        $this->stepFactory = $stepFactory;
        $this->stepLoader = $stepLoader;
        $this->yamlLoader = $yamlLoader;
    }

    /**
     * @param string $stepName
     * @param array $stepData
     * @param string[] $stepImportPaths
     * @param string[] $dataProviderImportPaths
     * @param PageInterface[] $pages
     *
     * @return StepInterface
     *
     * @throws StepBuilderInvalidPageElementReferenceException
     * @throws StepBuilderUnknownDataProviderImportException
     * @throws StepBuilderUnknownPageElementException
     * @throws StepBuilderUnknownPageImportException
     * @throws StepBuilderUnknownStepImportException
     * @throws YamlLoaderException
     * @throws MalformedPageElementReferenceException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     */
    public function build(
        string $stepName,
        array $stepData,
        array $stepImportPaths,
        array $dataProviderImportPaths,
        array $pages
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

                $data = $this->yamlLoader->loadArray($dataProviderImportPath);
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

                $page = $pages[$pageImportName] ?? null;

                if (null === $page) {
                    throw new StepBuilderUnknownPageImportException($stepName, $pageImportName, $pages);
                }

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
