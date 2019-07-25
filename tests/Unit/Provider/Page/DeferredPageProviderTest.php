<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Provider\Page;

use webignition\BasilModel\Page\PageInterface;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Loader\PageLoader;
use webignition\BasilParser\Provider\Page\DeferredPageProvider;
use webignition\BasilParser\Tests\Services\FixturePathFinder;

class DeferredPageProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testFindPageSuccess()
    {
        $pageProvider = new DeferredPageProvider(
            PageLoader::createLoader(),
            [
                'page_import_name' => FixturePathFinder::find('Page/example.com.heading.yml'),
            ]
        );

        $page = $pageProvider->findPage('page_import_name');

        $this->assertInstanceOf(PageInterface::class, $page);
    }

    public function testFindPageThrowsUnknownPageException()
    {
        $this->expectException(UnknownPageException::class);
        $this->expectExceptionMessage('Unknown page "page_import_name"');

        $pageProvider = new DeferredPageProvider(PageLoader::createLoader(), []);

        $pageProvider->findPage('page_import_name');
    }

    public function testFindPageThrowsYamlLoaderException()
    {
        $this->expectException(NonRetrievablePageException::class);
        $this->expectExceptionMessage('Cannot retrieve page "page_import_name" from "non-existent-file.yml"');

        $pageProvider = new DeferredPageProvider(
            PageLoader::createLoader(),
            [
                'page_import_name' => 'non-existent-file.yml',
            ]
        );

        $pageProvider->findPage('page_import_name');
    }
}
