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
use Php_Unit\Framework\InvalidArgumentException;
use Php_Unit\Framework\Mock_Object\Generator\Class_Is_Enumeration_Exception;
use Php_Unit\Framework\Mock_Object\Generator\Class_Is_Final_Exception;
use Php_Unit\Framework\Mock_Object\Generator\Duplicate_Method_Exception;
use Php_Unit\Framework\Mock_Object\Generator\Invalid_Method_Name_Exception;
use Php_Unit\Framework\Mock_Object\Generator\Name_Already_In_Use_Exception;
use Php_Unit\Framework\Mock_Object\Generator\Reflection_Exception;
use Php_Unit\Framework\Mock_Object\Generator\RuntimeException;
use Php_Unit\Framework\Mock_Object\Generator\Unknown_Type_Exception;
use Php_Unit\Framework\Test_Case;
/**
 * @template MockedType
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Mock_Builder extends Test_Double_Builder
{
    /**
     * @var ?class-string
     */
    private ?string $mock_class_name = null;
    /**
     * @param class-string|trait-string $type
     */
    public function __construct(private readonly Test_Case $test_case, string $type)
    {
        parent::__construct($type);
    }
    /**
     * Creates a mock object using a fluent interface.
     *
     * @throws ClassIsEnumerationException
     * @throws ClassIsFinalException
     * @throws DuplicateMethodException
     * @throws InvalidArgumentException
     * @throws InvalidMethodNameException
     * @throws NameAlreadyInUseException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws UnknownTypeException
     *
     * @return MockedType&MockObject
     */
    public function get_mock(): Mock_Object
    {
        $object = $this->get_test_double($this->mock_class_name, true);
        assert($object instanceof $this->type);
        assert($object instanceof Mock_Object);
        $this->test_case->register_mock_object($this->type, $object);
        return $object;
    }
    /**
     * Specifies the name for the mock class.
     *
     * @param class-string $name
     *
     * @return $this
     */
    public function set_mock_class_name(string $name): self
    {
        $this->mock_class_name = $name;
        return $this;
    }
}