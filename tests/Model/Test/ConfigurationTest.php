<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Model\Test;

use webignition\BasilParser\Model\Test\Configuration;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $browser = 'chrome';
        $uri = 'http://example.com/';

        $configuration = new Configuration($browser, $uri);

        $this->assertSame($browser, $configuration->getBrowser());
        $this->assertSame($uri, $configuration->getUrl());
    }
}
