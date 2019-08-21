<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilLoader\Tests\Services;

class FixturePathFinder
{
    const FIXTURES_RELATIVE_PATH = '/../Fixtures';

    public static function find(string $path): string
    {
        $realpath = realpath(self::getBasePath() . '/' . $path);

        if (false === $realpath) {
            throw new \RuntimeException('Fixture "' . $path . '" does not exist');
        }

        return $realpath;
    }

    public static function getBasePath(): string
    {
        return __DIR__ . self::FIXTURES_RELATIVE_PATH;
    }
}
