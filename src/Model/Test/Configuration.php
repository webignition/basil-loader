<?php

namespace webignition\BasilParser\Model\Test;

use Psr\Http\Message\UriInterface;

class Configuration implements ConfigurationInterface
{
    private $browser;
    private $url;

    public function __construct(string $browser, UriInterface $url)
    {
        $this->browser = $browser;
        $this->url = $url;
    }

    public function getBrowser(): string
    {
        return $this->browser;
    }

    public function getUrl(): UriInterface
    {
        return $this->url;
    }
}
