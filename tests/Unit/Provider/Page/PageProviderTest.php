<?php
/** @noinspection PhpUnhandledExceptionInspection */

namespace webignition\BasilParser\Tests\Unit\Provider\Page;

use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Provider\Page\PageProvider;

class PageProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testFindPageThrowsUnknownPageException()
    {
        $this->expectException(UnknownPageException::class);
        $this->expectExceptionMessage('Unknown page "page_import_name"');

        $pageProvider = new PageProvider([]);
        $pageProvider->findPage('page_import_name');
    }
}
