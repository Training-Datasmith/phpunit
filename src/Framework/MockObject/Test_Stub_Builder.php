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

use function assert;
use Php_Unit\Framework\Mock_Object\Generator\Class_Is_Enumeration_Exception;
use Php_Unit\Framework\Mock_Object\Generator\Class_Is_Final_Exception;
use Php_Unit\Framework\Mock_Object\Generator\Duplicate_Method_Exception;
use Php_Unit\Framework\Mock_Object\Generator\Invalid_Method_Name_Exception;
use Php_Unit\Framework\Mock_Object\Generator\Name_Already_In_Use_Exception;
use Php_Unit\Framework\Mock_Object\Generator\Reflection_Exception;
use Php_Unit\Framework\Mock_Object\Generator\RuntimeException;
use Php_Unit\Framework\Mock_Object\Generator\Unknown_Type_Exception;
/**
 * @template StubbedType
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Test_Stub_Builder extends Test_Double_Builder
{
    /**
     * @var ?class-string
     */
    private ?string $stub_class_name = null;
    /**
     * Creates a test stub using a fluent interface.
     *
     * @throws ClassIsEnumerationException
     * @throws ClassIsFinalException
     * @throws DuplicateMethodException
     * @throws InvalidMethodNameException
     * @throws NameAlreadyInUseException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws UnknownTypeException
     *
     * @return Stub&StubbedType
     */
    public function get_stub(): Stub
    {
        $object = $this->get_test_double($this->stub_class_name, false);
        assert($object instanceof $this->type);
        assert($object instanceof Stub);
        assert(!$object instanceof Mock_Object);
        return $object;
    }
    /**
     * Specifies the name for the mock class.
     *
     * @param class-string $name
     *
     * @return $this
     */
    public function set_stub_class_name(string $name): self
    {
        $this->stub_class_name = $name;
        return $this;
    }
}