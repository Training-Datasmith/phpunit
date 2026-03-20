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
use function class_exists;
use function in_array;
use Php_Unit\Framework\Test_Case;
use Php_Unit\Metadata\After;
use Php_Unit\Metadata\After_Class;
use Php_Unit\Metadata\Before;
use Php_Unit\Metadata\Before_Class;
use Php_Unit\Metadata\Parser\Registry;
use Php_Unit\Metadata\Post_Condition;
use Php_Unit\Metadata\Pre_Condition;
use Php_Unit\Runner\Hook_Method;
use Php_Unit\Runner\Hook_Method_Collection;
use Php_Unit\Util\Reflection;
use ReflectionClass;
use ReflectionMethod;
use function strtolower;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Hook_Methods
{
    /**
     * @var array<class-string, array{beforeClass: HookMethodCollection, before: HookMethodCollection, preCondition: HookMethodCollection, postCondition: HookMethodCollection, after: HookMethodCollection, afterClass: HookMethodCollection}>
     */
    private static array $hook_methods = [];
    /**
     * @param class-string<TestCase> $className
     *
     * @return array{beforeClass: HookMethodCollection, before: HookMethodCollection, preCondition: HookMethodCollection, postCondition: HookMethodCollection, after: HookMethodCollection, afterClass: HookMethodCollection}
     */
    public function hook_methods(string $class_name): array
    {
        if (!class_exists($class_name)) {
            return self::empty_hook_methods_array();
        }
        if (isset(self::$hook_methods[$class_name])) {
            return self::$hook_methods[$class_name];
        }
        self::$hook_methods[$class_name] = self::empty_hook_methods_array();
        foreach (Reflection::methods_declared_directly_in_test_class(new ReflectionClass($class_name)) as $method) {
            $method_name = $method->get_name();
            $metadata = Registry::parser()->for_method($class_name, $method_name);
            if ($method->is_static()) {
                if ($metadata->is_before_class()->is_not_empty()) {
                    $before_class = $metadata->is_before_class()->as_array()[0];
                    assert($before_class instanceof Before_Class);
                    self::$hook_methods[$class_name]['beforeClass']->add(new Hook_Method($method_name, $before_class->priority()));
                }
                if ($metadata->is_after_class()->is_not_empty()) {
                    $after_class = $metadata->is_after_class()->as_array()[0];
                    assert($after_class instanceof After_Class);
                    self::$hook_methods[$class_name]['afterClass']->add(new Hook_Method($method_name, $after_class->priority()));
                }
            }
            if ($metadata->is_before()->is_not_empty()) {
                $before = $metadata->is_before()->as_array()[0];
                assert($before instanceof Before);
                self::$hook_methods[$class_name]['before']->add(new Hook_Method($method_name, $before->priority()));
            }
            if ($metadata->is_pre_condition()->is_not_empty()) {
                $pre_condition = $metadata->is_pre_condition()->as_array()[0];
                assert($pre_condition instanceof Pre_Condition);
                self::$hook_methods[$class_name]['preCondition']->add(new Hook_Method($method_name, $pre_condition->priority()));
            }
            if ($metadata->is_post_condition()->is_not_empty()) {
                $post_condition = $metadata->is_post_condition()->as_array()[0];
                assert($post_condition instanceof Post_Condition);
                self::$hook_methods[$class_name]['postCondition']->add(new Hook_Method($method_name, $post_condition->priority()));
            }
            if ($metadata->is_after()->is_not_empty()) {
                $after = $metadata->is_after()->as_array()[0];
                assert($after instanceof After);
                self::$hook_methods[$class_name]['after']->add(new Hook_Method($method_name, $after->priority()));
            }
        }
        return self::$hook_methods[$class_name];
    }
    public function is_hook_method(ReflectionMethod $method): bool
    {
        $default_names = ['setupbeforeclass', 'setup', 'assertpreconditions', 'assertpostconditions', 'teardown', 'teardownafterclass'];
        if (in_array(strtolower($method->get_name()), $default_names, true)) {
            return true;
        }
        $metadata = Registry::parser()->for_method($method->get_declaring_class()->get_name(), $method->get_name());
        if ($metadata->is_before_class()->is_not_empty()) {
            return true;
        }
        if ($metadata->is_before()->is_not_empty()) {
            return true;
        }
        if ($metadata->is_pre_condition()->is_not_empty()) {
            return true;
        }
        if ($metadata->is_post_condition()->is_not_empty()) {
            return true;
        }
        if ($metadata->is_after()->is_not_empty()) {
            return true;
        }
        return $metadata->is_after_class()->is_not_empty();
    }
    /**
     * @return array{beforeClass: HookMethodCollection, before: HookMethodCollection, preCondition: HookMethodCollection, postCondition: HookMethodCollection, after: HookMethodCollection, afterClass: HookMethodCollection}
     */
    private function empty_hook_methods_array(): array
    {
        return ['beforeClass' => Hook_Method_Collection::default_before_class(), 'before' => Hook_Method_Collection::default_before(), 'preCondition' => Hook_Method_Collection::default_pre_condition(), 'postCondition' => Hook_Method_Collection::default_post_condition(), 'after' => Hook_Method_Collection::default_after(), 'afterClass' => Hook_Method_Collection::default_after_class()];
    }
}