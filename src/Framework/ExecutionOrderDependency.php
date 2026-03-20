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

use function array_filter;
use function array_map;
use function array_values;
use function assert;
use function count;
use function explode;
use function in_array;
use Php_Unit\Metadata\Depends_On_Class;
use Php_Unit\Metadata\Depends_On_Method;
use function str_contains;
use Stringable;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Execution_Order_Dependency implements Stringable
{
    private string $class_name = '';
    private string $method_name = '';
    public static function invalid(): self
    {
        return new self('', '', false, false);
    }
    public static function for_class(Depends_On_Class $metadata): self
    {
        return new self($metadata->class_name(), 'class', $metadata->deep_clone(), $metadata->shallow_clone());
    }
    public static function for_method(Depends_On_Method $metadata): self
    {
        return new self($metadata->class_name(), $metadata->method_name(), $metadata->deep_clone(), $metadata->shallow_clone());
    }
    /**
     * @param list<ExecutionOrderDependency> $dependencies
     *
     * @return list<ExecutionOrderDependency>
     */
    public static function filter_invalid(array $dependencies): array
    {
        return array_values(array_filter($dependencies, static fn(self $d): bool => $d->is_valid()));
    }
    /**
     * @param list<ExecutionOrderDependency> $existing
     * @param list<ExecutionOrderDependency> $additional
     *
     * @return list<ExecutionOrderDependency>
     */
    public static function merge_unique(array $existing, array $additional): array
    {
        $existing_targets = array_map(static fn(Execution_Order_Dependency $dependency): string => $dependency->get_target(), $existing);
        foreach ($additional as $dependency) {
            $additional_target = $dependency->get_target();
            if (in_array($additional_target, $existing_targets, true)) {
                continue;
            }
            $existing_targets[] = $additional_target;
            $existing[] = $dependency;
        }
        return $existing;
    }
    /**
     * @param list<ExecutionOrderDependency> $left
     * @param list<ExecutionOrderDependency> $right
     *
     * @return list<ExecutionOrderDependency>
     */
    public static function diff(array $left, array $right): array
    {
        if ($right === []) {
            return $left;
        }
        if ($left === []) {
            return [];
        }
        $diff = [];
        $right_targets = array_map(static fn(Execution_Order_Dependency $dependency): string => $dependency->get_target(), $right);
        foreach ($left as $dependency) {
            if (in_array($dependency->get_target(), $right_targets, true)) {
                continue;
            }
            $diff[] = $dependency;
        }
        return $diff;
    }
    public function __construct(string $class_or_callable_name, ?string $method_name = null, private readonly bool $deep_clone = false, private readonly bool $shallow_clone = false)
    {
        if ($class_or_callable_name === '') {
            return;
        }
        if (str_contains($class_or_callable_name, '::')) {
            assert(count(explode('::', $class_or_callable_name)) === 2);
            [$this->class_name, $this->method_name] = explode('::', $class_or_callable_name);
        } else {
            $this->class_name = $class_or_callable_name;
            $this->method_name = $method_name !== null && $method_name !== '' ? $method_name : 'class';
        }
    }
    public function __toString(): string
    {
        return $this->get_target();
    }
    /**
     * @phpstan-assert-if-true non-empty-string $this->getTarget()
     */
    public function is_valid(): bool
    {
        // Invalid dependencies can be declared and are skipped by the runner
        return $this->class_name !== '' && $this->method_name !== '';
    }
    public function shallow_clone(): bool
    {
        return $this->shallow_clone;
    }
    public function deep_clone(): bool
    {
        return $this->deep_clone;
    }
    public function target_is_class(): bool
    {
        return $this->method_name === 'class';
    }
    public function get_target(): string
    {
        return $this->is_valid() ? $this->class_name . '::' . $this->method_name : '';
    }
    public function get_target_class_name(): string
    {
        return $this->class_name;
    }
}