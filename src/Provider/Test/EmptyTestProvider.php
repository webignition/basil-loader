<?php

namespace webignition\BasilParser\Provider\Test;

use webignition\BasilParser\Exception\UnknownTestException;
use webignition\BasilParser\Model\Test\TestInterface;

class EmptyTestProvider implements TestProviderInterface
{
    /**
     * @param string $path
     *
     * @return TestInterface
     *
     * @throws UnknownTestException
     */
    public function findTest(string $path): TestInterface
    {
        throw new UnknownTestException($path);
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
