<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit;

use PHPUnit\Framework\TestCase;
use webignition\BasilLoader\Exception\InvalidPageException;
use webignition\BasilLoader\PageLoader;
use webignition\BasilLoader\Tests\Services\FixturePathFinder;
use webignition\BasilLoader\Validator\InvalidResult;
use webignition\BasilLoader\Validator\PageValidator;
use webignition\BasilLoader\Validator\ResultType;
use webignition\BasilModels\Model\Page\Page;
use webignition\BasilModels\Model\Page\PageInterface;

class PageLoaderTest extends TestCase
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
    public function testLoadSuccess(string $importName, string $path, PageInterface $expectedPage): void
    {
        $page = $this->loader->load($importName, $path);

        $this->assertEquals($expectedPage, $page);
    }

    /**
     * @return array<mixed>
     */
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

    public function testLoadThrowsInvalidPageException(): void
    {
        $importName = 'page_import_name';
        $path = FixturePathFinder::find('Empty/empty.yml');

        try {
            $this->loader->load($importName, $path);

            $this->fail('Exception not thrown');
        } catch (InvalidPageException $invalidPageException) {
            $expectedException = new InvalidPageException($importName, $path, new InvalidResult(
                [
                    'import_name' => $importName,
                    'path' => $path,
                    'data' => [],
                ],
                ResultType::PAGE,
                PageValidator::REASON_URL_EMPTY
            ));

            $this->assertEquals($expectedException, $invalidPageException);
        }
    }
}
