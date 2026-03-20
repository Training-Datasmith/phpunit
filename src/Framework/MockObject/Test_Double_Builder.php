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
namespace Php_Unit\Framework\Mock_Object;

use function array_merge;
use Php_Unit\Framework\Mock_Object\Generator\Generator;
use Php_Unit\Framework\Mock_Object\Generator\Reflection_Exception;
use ReflectionClass;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract class Test_Double_Builder
{
    /**
     * @var list<non-empty-string>
     */
    protected array $methods = [];
    protected bool $empty_methods_array = false;
    /**
     * @var array<mixed>
     */
    protected array $constructor_args = [];
    protected bool $original_constructor = true;
    protected bool $original_clone = true;
    protected bool $return_value_generation = true;
    /**
     * @param class-string|trait-string $type
     */
    public function __construct(protected readonly string $type)
    {
    }
    /**
     * Specifies the subset of methods to mock, requiring each to exist in the class.
     *
     * @param list<non-empty-string> $methods
     *
     * @throws CannotUseOnlyMethodsException
     * @throws ReflectionException
     *
     * @return $this
     */
    public function only_methods(array $methods): self
    {
        if ($methods === []) {
            $this->empty_methods_array = true;
            return $this;
        }
        try {
            $reflector = new ReflectionClass($this->type);
            // @codeCoverageIgnoreStart
        } catch (\Reflection_Exception $e) {
            throw new Reflection_Exception($e->get_message(), $e->get_code(), $e);
            // @codeCoverageIgnoreEnd
        }
        foreach ($methods as $method) {
            if (!$reflector->has_method($method)) {
                throw new Cannot_Use_Only_Methods_Exception($this->type, $method);
            }
        }
        $this->methods = array_merge($this->methods, $methods);
        return $this;
    }
    /**
     * Specifies the arguments for the constructor.
     *
     * @param array<mixed> $arguments
     *
     * @return $this
     */
    public function set_constructor_args(array $arguments): self
    {
        $this->constructor_args = $arguments;
        return $this;
    }
    /**
     * Disables the invocation of the original constructor.
     *
     * @return $this
     */
    public function disable_original_constructor(): self
    {
        $this->original_constructor = false;
        return $this;
    }
    /**
     * Enables the invocation of the original constructor.
     *
     * @return $this
     */
    public function enable_original_constructor(): self
    {
        $this->original_constructor = true;
        return $this;
    }
    /**
     * Disables the invocation of the original clone constructor.
     *
     * @return $this
     */
    public function disable_original_clone(): self
    {
        $this->original_clone = false;
        return $this;
    }
    /**
     * Enables the invocation of the original clone constructor.
     *
     * @return $this
     */
    public function enable_original_clone(): self
    {
        $this->original_clone = true;
        return $this;
    }
    /**
     * @return $this
     */
    public function enable_auto_return_value_generation(): self
    {
        $this->return_value_generation = true;
        return $this;
    }
    /**
     * @return $this
     */
    public function disable_auto_return_value_generation(): self
    {
        $this->return_value_generation = false;
        return $this;
    }
    protected function get_test_double(?string $test_double_class_name, bool $mock_object): Mock_Object|Stub
    {
        return (new Generator())->test_double($this->type, $mock_object, !$this->empty_methods_array ? $this->methods : null, $this->constructor_args, $test_double_class_name ?? '', $this->original_constructor, $this->original_clone, $this->return_value_generation);
    }
}