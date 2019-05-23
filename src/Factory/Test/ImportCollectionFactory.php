<?php

namespace webignition\BasilParser\Factory\Test;

use webignition\BasilParser\Model\Test\ImportCollection;
use webignition\BasilParser\Model\Test\ImportCollectionInterface;

class ImportCollectionFactory
{
    const KEY_PAGES = 'config';
    const KEY_STEPS = 'imports';

    public function createFromImportCollectionData(array $importCollectionData): ImportCollectionInterface
    {
        $pageImportPaths = $importCollectionData[self::KEY_PAGES] ?? [];
        $stepImportPaths = $importCollectionData[self::KEY_STEPS] ?? [];

        $pageImportPaths = is_array($pageImportPaths) ? $pageImportPaths : [];
        $stepImportPaths = is_array($stepImportPaths) ? $stepImportPaths : [];

        return new ImportCollection($pageImportPaths, $stepImportPaths);
    }
}
