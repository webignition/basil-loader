<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\PageCollection;

use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\PageCollection\EmptyPageCollection;

class EmptyPageCollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testFindPageThrowsUnknownPageException()
    {
        $this->expectException(UnknownPageException::class);
        $this->expectExceptionMessage('Unknown page "page_import_name"');

        $pageCollection = new EmptyPageCollection();

        $pageCollection->findPage('page_import_name');
    }
}
