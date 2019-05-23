<?php

namespace webignition\BasilParser\Factory\Test;

use webignition\BasilParser\Model\Test\ImportCollection;

class ImportCollectionFactory
{
    const KEY_PAGES = 'config';
    const KEY_STEPS = 'imports';

    public function createFromImportCollectionData(array $importCollectionData)
    {
        $pageImportPaths = $importCollectionData[self::KEY_PAGES] ?? [];
        $stepImportPaths = $importCollectionData[self::KEY_STEPS] ?? [];

        $pageImportPaths = is_array($pageImportPaths) ? $pageImportPaths : [];
        $stepImportPaths = is_array($stepImportPaths) ? $stepImportPaths : [];

        return new ImportCollection($pageImportPaths, $stepImportPaths);
    }
}
