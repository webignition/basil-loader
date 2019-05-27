<?php

namespace webignition\BasilParser\DataSetCollection;

use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Loader\DataSetLoader;
use webignition\BasilParser\Loader\YamlLoaderException;
use webignition\BasilParser\Model\DataSet\DataSetInterface;

class DeferredDataSetCollection implements DataSetCollectionInterface
{
    private $dataSetLoader;
    private $importPaths;
    private $dataSetCollections = [];

    public function __construct(DataSetLoader $dataSetLoader, array $importPaths)
    {
        $this->dataSetLoader = $dataSetLoader;
        $this->importPaths = $importPaths;
    }

    /**
     * @param string $importName
     *
     * @return DataSetInterface[]
     *
     * @throws NonRetrievableDataProviderException
     * @throws UnknownDataProviderException
     */
    public function findDataSetCollection(string $importName): array
    {
        $dataSetCollection = $this->dataSetCollections[$importName] ?? null;

        if (null === $dataSetCollection) {
            $dataSetCollection = $this->retrieveDataSetCollection($importName);
            $this->dataSetCollections[$importName] = $dataSetCollection;
        }

        return $dataSetCollection;
    }

    /**
     * @param string $importName
     *
     * @return DataSetInterface[]
     *
     * @throws NonRetrievableDataProviderException
     * @throws UnknownDataProviderException
     */
    private function retrieveDataSetCollection(string $importName): array
    {
        $importPath = $this->importPaths[$importName] ?? null;

        if (null === $importPath) {
            throw new UnknownDataProviderException($importName);
        }

        try {
            return $this->dataSetLoader->load($importPath);
        } catch (YamlLoaderException $yamlLoaderException) {
            throw new NonRetrievableDataProviderException($importName, $importPath, $yamlLoaderException);
        }
    }
}
