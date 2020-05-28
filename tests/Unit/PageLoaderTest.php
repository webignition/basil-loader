<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit;

use webignition\BasilDataValidator\PageValidator;
use webignition\BasilDataValidator\ResultType;
use webignition\BasilLoader\Exception\InvalidPageException;
use webignition\BasilLoader\PageLoader;
use webignition\BasilLoader\Tests\Services\FixturePathFinder;
use webignition\BasilModels\Page\Page;
use webignition\BasilModels\Page\PageInterface;
use webignition\BasilValidationResult\InvalidResult;

class PageLoaderTest extends \PHPUnit\Framework\TestCase
{
    private PageLoader $loader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = PageLoader::createLoader();
    }

    /**
     * @dataProvider loadSuccessDataProvider
     */
    public function testLoadSuccess(string $importName, string $path, PageInterface $expectedPage)
    {
        $page = $this->loader->load($importName, $path);

        $this->assertEquals($expectedPage, $page);
    }

    public function loadSuccessDataProvider(): array
    {
        return [
            'url only' => [
                'importName' => 'import_name',
                'path' => FixturePathFinder::find('Page/example.com.url-only.yml'),
                'expectedPage' => new Page('import_name', 'https://example.com'),
            ],
            'url and element references' => [
                'importName' => 'import_name',
                'path' => FixturePathFinder::find('Page/example.com.form.yml'),
                'expectedPage' => new Page(
                    'import_name',
                    'https://example.com',
                    [
                        'form' => '$".form"',
                        'input' => '$form >> $".input"',
                    ]
                ),
            ],
        ];
    }

    public function testLoadThrowsInvalidPageException()
    {
        $importName = 'page_import_name';
        $path = FixturePathFinder::find('Empty/empty.yml');

        try {
            $this->loader->load($importName, $path);

            $this->fail('Exception not thrown');
        } catch (InvalidPageException $invalidPageException) {
            $expectedException = new InvalidPageException($importName, $path, new InvalidResult(
                new Page('page_import_name', ''),
                ResultType::PAGE,
                PageValidator::REASON_URL_EMPTY
            ));

            $this->assertEquals($expectedException, $invalidPageException);
        }
    }
}
