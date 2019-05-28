<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Provider\Page;

use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;

class EmptyPageProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testFindPageThrowsUnknownPageException()
    {
        $this->expectException(UnknownPageException::class);
        $this->expectExceptionMessage('Unknown page "page_import_name"');

        $pageProvider = new EmptyPageProvider();

        $pageProvider->findPage('page_import_name');
    }
}
