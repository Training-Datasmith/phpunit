<?php

declare (strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Php_Unit\Framework;

use Countable;
/**
 * Represents a runnable test (a single test case, a test suite, or a PHPT test).
 *
 * All items that can be scheduled and executed by the Runner implement this interface.
 * The {@see Countable} super-interface exposes the number of individual test methods
 * that the implementor represents (1 for a TestCase, N for a TestSuite).
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
interface Test extends Countable
{
    /**
     * Executes the test.
     *
     * For a {@see TestCase}, this runs a single test method, capturing the result
     * and emitting the appropriate events. For a {@see TestSuite}, this iterates
     * over all contained tests and runs each in turn.
     *
     * @throws \Throwable from test failures, errors, or timeouts — callers should
     *                    catch and record rather than propagate
     */
    public function run(): void;
}