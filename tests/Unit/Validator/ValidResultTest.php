<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use webignition\BasilLoader\Validator\ValidResult;

class ValidResultTest extends TestCase
{
    public function testCreate(): void
    {
        $subject = new \stdClass();

        $result = new ValidResult($subject);

        $this->assertTrue($result->getIsValid());
        $this->assertSame($subject, $result->getSubject());
    }
}
