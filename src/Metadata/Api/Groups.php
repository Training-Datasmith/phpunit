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

use function array_flip;
use function array_key_exists;
use function array_unique;
use function assert;
use Php_Unit\Framework\Test_Size\Test_Size;
use Php_Unit\Metadata\Covers_Class;
use Php_Unit\Metadata\Covers_Function;
use Php_Unit\Metadata\Group;
use Php_Unit\Metadata\Parser\Registry;
use Php_Unit\Metadata\Requires_Php_Extension;
use Php_Unit\Metadata\Uses_Class;
use Php_Unit\Metadata\Uses_Function;
use function strtolower;
use function trim;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Groups
{
    /**
     * @var array<string, list<non-empty-string>>
     */
    private static array $group_cache = [];
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     *
     * @return list<non-empty-string>
     */
    public function groups(string $class_name, string $method_name, bool $include_virtual = true): array
    {
        $key = $class_name . '::' . $method_name . '::' . $include_virtual;
        if (array_key_exists($key, self::$group_cache)) {
            return self::$group_cache[$key];
        }
        $groups = [];
        foreach (Registry::parser()->for_class_and_method($class_name, $method_name)->is_group() as $group) {
            assert($group instanceof Group);
            $groups[] = $group->group_name();
        }
        if (!$include_virtual) {
            return self::$group_cache[$key] = array_unique($groups);
        }
        foreach (Registry::parser()->for_class_and_method($class_name, $method_name) as $metadata) {
            if ($metadata->is_covers_class()) {
                assert($metadata instanceof Covers_Class);
                $groups[] = '__phpunit_covers_' . $this->canonicalize_name($metadata->class_name());
                continue;
            }
            if ($metadata->is_covers_function()) {
                assert($metadata instanceof Covers_Function);
                $groups[] = '__phpunit_covers_' . $this->canonicalize_name($metadata->function_name());
                continue;
            }
            if ($metadata->is_uses_class()) {
                assert($metadata instanceof Uses_Class);
                $groups[] = '__phpunit_uses_' . $this->canonicalize_name($metadata->class_name());
                continue;
            }
            if ($metadata->is_uses_function()) {
                assert($metadata instanceof Uses_Function);
                $groups[] = '__phpunit_uses_' . $this->canonicalize_name($metadata->function_name());
                continue;
            }
            if ($metadata->is_requires_php_extension()) {
                assert($metadata instanceof Requires_Php_Extension);
                $groups[] = '__phpunit_requires_php_extension' . $this->canonicalize_name($metadata->extension());
            }
        }
        return self::$group_cache[$key] = array_unique($groups);
    }
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public function size(string $class_name, string $method_name): Test_Size
    {
        $groups = array_flip($this->groups($class_name, $method_name));
        if (isset($groups['large'])) {
            return Test_Size::large();
        }
        if (isset($groups['medium'])) {
            return Test_Size::medium();
        }
        if (isset($groups['small'])) {
            return Test_Size::small();
        }
        return Test_Size::unknown();
    }
    private function canonicalize_name(string $name): string
    {
        return strtolower(trim($name, '\\'));
    }
}