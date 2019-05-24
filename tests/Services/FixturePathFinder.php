<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Services;

class FixturePathFinder
{
    const FIXTURES_RELATIVE_PATH = '/../Fixtures/';

    public static function find(string $path): string
    {
        $realpath = realpath(__DIR__ . self::FIXTURES_RELATIVE_PATH . $path);

        if (false === $realpath) {
            throw new \RuntimeException('Fixture "' . $path . '" does not exist');
        }

        return $realpath;
    }
}
