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
namespace Php_Unit\Metadata\Parser;

use function assert;
use function class_exists;
use function method_exists;
use Php_Unit\Metadata\Metadata_Collection;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Caching_Parser implements Parser
{
    /**
     * @var array<class-string, MetadataCollection>
     */
    private array $class_cache = [];
    /**
     * @var array<non-empty-string, MetadataCollection>
     */
    private array $method_cache = [];
    /**
     * @var array<non-empty-string, MetadataCollection>
     */
    private array $class_and_method_cache = [];
    public function __construct(private readonly Parser $reader)
    {
    }
    /**
     * @param class-string $className
     */
    public function for_class(string $class_name): Metadata_Collection
    {
        assert(class_exists($class_name));
        if (isset($this->class_cache[$class_name])) {
            return $this->class_cache[$class_name];
        }
        $this->class_cache[$class_name] = $this->reader->for_class($class_name);
        return $this->class_cache[$class_name];
    }
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public function for_method(string $class_name, string $method_name): Metadata_Collection
    {
        assert(class_exists($class_name));
        assert(method_exists($class_name, $method_name));
        $key = $class_name . '::' . $method_name;
        if (isset($this->method_cache[$key])) {
            return $this->method_cache[$key];
        }
        $this->method_cache[$key] = $this->reader->for_method($class_name, $method_name);
        return $this->method_cache[$key];
    }
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public function for_class_and_method(string $class_name, string $method_name): Metadata_Collection
    {
        $key = $class_name . '::' . $method_name;
        if (isset($this->class_and_method_cache[$key])) {
            return $this->class_and_method_cache[$key];
        }
        $this->class_and_method_cache[$key] = $this->for_class($class_name)->merge_with($this->for_method($class_name, $method_name));
        return $this->class_and_method_cache[$key];
    }
}