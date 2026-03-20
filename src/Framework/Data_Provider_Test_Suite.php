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

use function assert;
use function class_exists;
use function count;
use function explode;
use Php_Unit\Framework\Test_Size\Test_Size;
use Php_Unit\Metadata\Api\Groups;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Data_Provider_Test_Suite extends Test_Suite
{
    /**
     * @var list<ExecutionOrderDependency>
     */
    private array $dependencies = [];
    /**
     * @var ?non-empty-list<ExecutionOrderDependency>
     */
    private ?array $provided_tests = null;
    /**
     * @param list<ExecutionOrderDependency> $dependencies
     */
    public function set_dependencies(array $dependencies): void
    {
        $this->dependencies = $dependencies;
        foreach ($this->tests() as $test) {
            if (!$test instanceof Test_Case) {
                continue;
            }
            $test->set_dependencies($dependencies);
        }
    }
    /**
     * @return non-empty-list<ExecutionOrderDependency>
     */
    public function provides(): array
    {
        if ($this->provided_tests === null) {
            $this->provided_tests = [new Execution_Order_Dependency($this->name())];
        }
        return $this->provided_tests;
    }
    /**
     * @return list<ExecutionOrderDependency>
     */
    public function requires(): array
    {
        // A DataProviderTestSuite does not have to traverse its child tests
        // as these are inherited and cannot reference dataProvider rows directly
        return $this->dependencies;
    }
    /**
     * Returns the size of each test created using the data provider(s).
     */
    public function size(): Test_Size
    {
        assert(count(explode('::', $this->name())) === 2);
        [$class_name, $method_name] = explode('::', $this->name());
        assert(class_exists($class_name));
        assert($method_name !== '');
        return (new Groups())->size($class_name, $method_name);
    }
}