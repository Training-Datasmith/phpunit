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
namespace Php_Unit\Metadata\Api;

use function assert;
use Php_Unit\Framework\Test_Case;
use Php_Unit\Metadata\Covers_Class;
use Php_Unit\Metadata\Covers_Classes_That_Extend_Class;
use Php_Unit\Metadata\Covers_Classes_That_Implement_Interface;
use Php_Unit\Metadata\Covers_Function;
use Php_Unit\Metadata\Covers_Method;
use Php_Unit\Metadata\Covers_Namespace;
use Php_Unit\Metadata\Covers_Trait;
use Php_Unit\Metadata\Parser\Registry;
use Php_Unit\Metadata\Uses_Class;
use Php_Unit\Metadata\Uses_Classes_That_Extend_Class;
use Php_Unit\Metadata\Uses_Classes_That_Implement_Interface;
use Php_Unit\Metadata\Uses_Function;
use Php_Unit\Metadata\Uses_Method;
use Php_Unit\Metadata\Uses_Namespace;
use Php_Unit\Metadata\Uses_Trait;
use Sebastian_Bergmann\Code_Coverage\Test\Target\Target;
use Sebastian_Bergmann\Code_Coverage\Test\Target\Target_Collection;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Code_Coverage
{
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public function covers_targets(string $class_name, string $method_name): Target_Collection
    {
        $targets = [];
        foreach (Registry::parser()->for_class_and_method($class_name, $method_name) as $metadata) {
            if ($metadata->is_covers_namespace()) {
                assert($metadata instanceof Covers_Namespace);
                $targets[] = Target::for_namespace($metadata->namespace());
            }
            if ($metadata->is_covers_class()) {
                assert($metadata instanceof Covers_Class);
                $targets[] = Target::for_class($metadata->class_name());
            }
            if ($metadata->is_covers_classes_that_extend_class()) {
                assert($metadata instanceof Covers_Classes_That_Extend_Class);
                $targets[] = Target::for_classes_that_extend_class($metadata->class_name());
            }
            if ($metadata->is_covers_classes_that_implement_interface()) {
                assert($metadata instanceof Covers_Classes_That_Implement_Interface);
                $targets[] = Target::for_classes_that_implement_interface($metadata->interface_name());
            }
            if ($metadata->is_covers_method()) {
                assert($metadata instanceof Covers_Method);
                $targets[] = Target::for_method($metadata->class_name(), $metadata->method_name());
            }
            if ($metadata->is_covers_function()) {
                assert($metadata instanceof Covers_Function);
                $targets[] = Target::for_function($metadata->function_name());
            }
            if ($metadata->is_covers_trait()) {
                assert($metadata instanceof Covers_Trait);
                $targets[] = Target::for_trait($metadata->trait_name());
            }
        }
        return Target_Collection::from_array($targets);
    }
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public function uses_targets(string $class_name, string $method_name): Target_Collection
    {
        $targets = [];
        foreach (Registry::parser()->for_class_and_method($class_name, $method_name) as $metadata) {
            if ($metadata->is_uses_namespace()) {
                assert($metadata instanceof Uses_Namespace);
                $targets[] = Target::for_namespace($metadata->namespace());
            }
            if ($metadata->is_uses_class()) {
                assert($metadata instanceof Uses_Class);
                $targets[] = Target::for_class($metadata->class_name());
            }
            if ($metadata->is_uses_classes_that_extend_class()) {
                assert($metadata instanceof Uses_Classes_That_Extend_Class);
                $targets[] = Target::for_classes_that_extend_class($metadata->class_name());
            }
            if ($metadata->is_uses_classes_that_implement_interface()) {
                assert($metadata instanceof Uses_Classes_That_Implement_Interface);
                $targets[] = Target::for_classes_that_implement_interface($metadata->interface_name());
            }
            if ($metadata->is_uses_method()) {
                assert($metadata instanceof Uses_Method);
                $targets[] = Target::for_method($metadata->class_name(), $metadata->method_name());
            }
            if ($metadata->is_uses_function()) {
                assert($metadata instanceof Uses_Function);
                $targets[] = Target::for_function($metadata->function_name());
            }
            if ($metadata->is_uses_trait()) {
                assert($metadata instanceof Uses_Trait);
                $targets[] = Target::for_trait($metadata->trait_name());
            }
        }
        return Target_Collection::from_array($targets);
    }
    public function should_code_coverage_be_collected_for(Test_Case $test): bool
    {
        $parser = Registry::parser();
        if ($parser->for_class($test::class)->is_covers_nothing()->is_not_empty()) {
            return false;
        }
        return true;
    }
}