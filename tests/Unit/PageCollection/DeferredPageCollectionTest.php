<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\PageCollection;

use Symfony\Component\Yaml\Parser as YamlParser;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Factory\PageFactory;
use webignition\BasilParser\Loader\PageLoader;
use webignition\BasilParser\Loader\YamlLoader;
use webignition\BasilParser\Model\Page\PageInterface;
use webignition\BasilParser\PageCollection\DeferredPageCollection;
use webignition\BasilParser\Tests\Services\FixturePathFinder;

class DeferredPageCollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testFindPageSuccess()
    {
        $pageCollection = new DeferredPageCollection($this->createPageLoader(), [
            'page_import_name' => FixturePathFinder::find('Page/example.com.heading.yml'),
        ]);

        $page = $pageCollection->findPage('page_import_name');

        $this->assertInstanceOf(PageInterface::class, $page);
    }

    public function testFindPageThrowsUnknownPageException()
    {
        $this->expectException(UnknownPageException::class);
        $this->expectExceptionMessage('Unknown page "page_import_name"');

        $pageCollection = new DeferredPageCollection($this->createPageLoader(), []);

        $pageCollection->findPage('page_import_name');
    }

    public function testFindPageThrowsYamlLoaderException()
    {
        $this->expectException(NonRetrievablePageException::class);
        $this->expectExceptionMessage('Cannot retrieve page "page_import_name" from "non-existent-file.yml"');

        $pageCollection = new DeferredPageCollection($this->createPageLoader(), [
            'page_import_name' => 'non-existent-file.yml',
        ]);

        $pageCollection->findPage('page_import_name');
    }

    private function createPageLoader()
    {
        $yamlParser = new YamlParser();

        $yamlLoader = new YamlLoader($yamlParser);
        $pageFactory = new PageFactory();

        return new PageLoader($yamlLoader, $pageFactory);
    }
}
