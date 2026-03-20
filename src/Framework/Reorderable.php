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

/**
 * Implemented by tests that participate in dependency-ordered execution.
 *
 * The Runner uses this interface to sort tests so that tests which provide
 * results that other tests depend on run first. Dependency resolution is
 * based on the string IDs returned by {@see sortId()} and the dependency
 * declarations returned by {@see provides()} and {@see requires()}.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This interface is not covered by the backward compatibility promise for PHPUnit
 */
interface Reorderable
{
    /**
     * Returns a unique, stable identifier used to sort and reference this test.
     *
     * For a {@see TestCase} this is typically "ClassName::methodName" or
     * "ClassName::methodName with data set #N".
     *
     * @return string Fully-qualified, unique test identifier
     */
    public function sort_id(): string;
    /**
     * Returns the list of test identifiers that this test provides results for.
     *
     * Tests that declare a dependency on any of these identifiers will be
     * scheduled after this test and receive its result via $this->dependencyInput.
     *
     * @return list<ExecutionOrderDependency> Dependencies this test satisfies
     */
    public function provides(): array;
    /**
     * Returns the list of test identifiers that this test depends on.
     *
     * The Runner will schedule this test after all listed dependencies have run.
     * If a required test fails, this test may be marked as skipped.
     *
     * @return list<ExecutionOrderDependency> Tests that must run before this one
     */
    public function requires(): array;
}