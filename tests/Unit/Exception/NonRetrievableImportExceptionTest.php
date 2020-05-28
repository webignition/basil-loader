<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit\Exception;

use webignition\BasilContextAwareException\ExceptionContext\ExceptionContext;
use webignition\BasilContextAwareException\ExceptionContext\ExceptionContextInterface;
use webignition\BasilLoader\Exception\NonRetrievableImportException;
use webignition\BasilLoader\Exception\YamlLoaderException;

class NonRetrievableImportExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testGetExceptionContext()
    {
        $exception = new NonRetrievableImportException(
            'page',
            'page_import_name',
            '../Page/invalid.yml',
            \Mockery::mock(YamlLoaderException::class)
        );

        $this->assertEquals(new ExceptionContext(), $exception->getExceptionContext());

        $exception->applyExceptionContext([
            ExceptionContextInterface::KEY_TEST_NAME => 'test name',
            ExceptionContextInterface::KEY_STEP_NAME => 'step name',
            ExceptionContextInterface::KEY_CONTENT => 'content',
        ]);

        $this->assertEquals(
            new ExceptionContext([
                ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                ExceptionContextInterface::KEY_CONTENT => 'content',
            ]),
            $exception->getExceptionContext()
        );
    }
}
