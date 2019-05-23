<?php

namespace webignition\BasilParser\Builder;

use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Loader\PageLoader;
use webignition\BasilParser\Loader\StepLoader;
use webignition\BasilParser\Loader\YamlLoader;
use webignition\BasilParser\Loader\YamlLoaderException;
use webignition\BasilParser\Model\Step\StepInterface;

class StepBuilder
{
    const KEY_USE = 'use';
    const KEY_DATA = 'data';
    const KEY_ELEMENTS = 'elements';

    private $stepFactory;
    private $pageLoader;
    private $stepLoader;
    private $yamlLoader;

    public function __construct(
        StepFactory $stepFactory,
        PageLoader $pageLoader,
        StepLoader $stepLoader,
        YamlLoader $yamlLoader
    ) {
        $this->stepFactory = $stepFactory;
        $this->pageLoader = $pageLoader;
        $this->stepLoader = $stepLoader;
        $this->yamlLoader = $yamlLoader;
    }

    /**
     * @param string $stepName
     * @param array $stepData
     * @param string[] $stepImportPaths
     * @param string[] $dataProviderImportPaths
     * @param string[] $pageImportPaths
     *
     * @return StepInterface
     *
     * @throws UnknownDataProviderImportException
     * @throws UnknownStepImportException
     * @throws YamlLoaderException
     * @throws UnknownPageImportException
     * @throws UnknownPageElementException
     */
    public function build(
        string $stepName,
        array $stepData,
        array $stepImportPaths,
        array $dataProviderImportPaths,
        array $pageImportPaths
    ) {
        $importName = $stepData[self::KEY_USE] ?? null;
        if (null === $importName) {
            $step = $this->stepFactory->createFromStepData($stepData);
        } else {
            $importPath = $stepImportPaths[$importName] ?? null;

            if (null === $importPath) {
                throw new UnknownStepImportException($stepName, $importName, $stepImportPaths);
            }

            $step = $this->stepLoader->load($importPath);
        }

        $data = $stepData[self::KEY_DATA] ?? null;
        if (null !== $data) {
            if (is_string($data)) {
                $importName = $data;
                $importPath = $dataProviderImportPaths[$importName] ?? null;

                if (null === $importPath) {
                    throw new UnknownDataProviderImportException($stepName, $importName, $dataProviderImportPaths);
                }

                $data = $this->yamlLoader->loadArray($importPath);
            }

            if (is_array($data)) {
                $step = $step->withDataSets($data);
            }
        }

        $elementUses = $stepData[self::KEY_ELEMENTS] ?? null;
        if (null !== $elementUses) {
            $elementIdentifiers = [];

            foreach ($elementUses as $elementName => $pageModelElementReference) {
                list($importName, $elementName) = $this->findPageImportNameAndElementName($pageModelElementReference);

                $importPath = $pageImportPaths[$importName] ?? null;

                if (null === $importPath) {
                    throw new UnknownPageImportException($stepName, $importName, $pageImportPaths);
                }

                $page = $this->pageLoader->load($importPath);
                $elementIdentifier = $page->getElementIdentifier($elementName);

                if (null === $elementIdentifier) {
                    throw new UnknownPageElementException(
                        $stepName,
                        $importName,
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

    private function findPageImportNameAndElementName(string $pageModelElementReference)
    {
        $parts = explode('.', $pageModelElementReference);

        return [
            $parts[0],
            $parts[2],
        ];
    }
}
