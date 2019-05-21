<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Model\Test;

use Nyholm\Psr7\Uri;
use webignition\BasilParser\Model\Test\Configuration;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $browser = 'chrome';
        $uri = new Uri('http://example.com/');

        $configuration = new Configuration($browser, $uri);

        $this->assertSame($browser, $configuration->getBrowser());
        $this->assertSame($uri, $configuration->getUrl());
    }
}
