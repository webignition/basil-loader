<?php

namespace webignition\BasilParser\Provider\Test;

use webignition\BasilModel\Test\TestInterface;
use webignition\BasilParser\Exception\UnknownTestException;

class PopulatedTestProvider implements TestProviderInterface
{
    private $tests = [];

    public function __construct(array $tests)
    {
        foreach ($tests as $path => $test) {
            if ($test instanceof TestInterface) {
                $this->tests[$path] = $test;
            }
        }
    }

    /**
     * @param string $path
     *
     * @return TestInterface
     *
     * @throws UnknownTestException
     */
    public function findTest(string $path): TestInterface
    {
        $test = $this->tests[$path] ?? null;

        if (null === $test) {
            throw new UnknownTestException($path);
        }

        return $test;
    }

    /**
     * @param string[] $paths
     *
     * @return TestInterface[]
     *
     * @throws UnknownTestException
     */
    public function findCollection(array $paths): array
    {
        $tests = [];

        foreach ($paths as $path) {
            if (is_string($path)) {
                $tests[] = $this->findTest($path);
            }
        }

        return $tests;
    }
}
