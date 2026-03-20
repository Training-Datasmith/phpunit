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

use function array_map;
use function implode;
use Php_Unit\Framework\Self_Describing;
use Php_Unit\Util\Exporter;
use function sprintf;
use function str_starts_with;
use function strtolower;
use function substr;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Invocation implements Self_Describing
{
    private string $return_type;
    private bool $is_return_type_nullable;
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     * @param array<mixed>     $parameters
     */
    public function __construct(private string $class_name, private string $method_name, private array $parameters, string $return_type, private Mock_Object_Internal|Stub_Internal $object)
    {
        if (strtolower($this->method_name) === '__tostring') {
            $return_type = 'string';
        }
        if (str_starts_with($return_type, '?')) {
            $return_type = substr($return_type, 1);
            $this->is_return_type_nullable = true;
        } else {
            $this->is_return_type_nullable = false;
        }
        $this->return_type = $return_type;
    }
    /**
     * @return class-string
     */
    public function class_name(): string
    {
        return $this->class_name;
    }
    /**
     * @return non-empty-string
     */
    public function method_name(): string
    {
        return $this->method_name;
    }
    /**
     * @return array<mixed>
     */
    public function parameters(): array
    {
        return $this->parameters;
    }
    /**
     * @throws Exception
     */
    public function generate_return_value(): mixed
    {
        if ($this->return_type === 'never') {
            throw new Never_Returning_Method_Exception($this->class_name, $this->method_name);
        }
        if ($this->is_return_type_nullable) {
            return null;
        }
        return (new Return_Value_Generator())->generate($this->class_name, $this->method_name, $this->object, $this->return_type);
    }
    public function to_string(): string
    {
        return sprintf('%s::%s(%s)%s', $this->class_name, $this->method_name, implode(', ', array_map(Exporter::shortened_export(...), $this->parameters)), $this->return_type !== '' ? sprintf(': %s', $this->return_type) : '');
    }
    public function object(): Mock_Object_Internal|Stub_Internal
    {
        return $this->object;
    }
}