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
namespace Php_Unit\Framework\Mock_Object\Generator;

use function array_key_exists;
use function assert;
use function count;
use function explode;
use function implode;
use function is_object;
use function is_string;
use function preg_match;
use function preg_replace;
use ReflectionMethod;
use ReflectionParameter;
use Sebastian_Bergmann\Type\Reflection_Mapper;
use Sebastian_Bergmann\Type\Type;
use Sebastian_Bergmann\Type\Unknown_Type;
use function str_contains;
use function strlen;
use function strpos;
use function substr;
use function substr_count;
use function trim;
use function var_export;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Doubled_Method
{
    use Template_Loader;
    private readonly Type $return_type;
    /**
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public static function from_reflection(ReflectionMethod $method): self
    {
        if ($method->is_private()) {
            $modifier = 'private';
        } elseif ($method->is_protected()) {
            $modifier = 'protected';
        } else {
            $modifier = 'public';
        }
        if ($method->is_static()) {
            $modifier .= ' static';
        }
        if ($method->returns_reference()) {
            $reference = '&';
        } else {
            $reference = '';
        }
        $doc_comment = $method->get_doc_comment();
        if (is_string($doc_comment) && preg_match('#\*[ \t]*+@deprecated[ \t]*+(.*?)\r?+\n[ \t]*+\*(?:[ \t]*+@|/$)#s', $doc_comment, $deprecation) > 0) {
            $deprecation = trim((string) preg_replace('#[ \t]*\r?\n[ \t]*+\*[ \t]*+#', ' ', $deprecation[1]));
        } else {
            $deprecation = null;
        }
        return new self($method->get_declaring_class()->get_name(), $method->get_name(), $modifier, self::method_parameters_for_declaration($method), self::method_parameters_for_call($method), self::method_parameters_default_values($method), count($method->get_parameters()), (new Reflection_Mapper())->from_return_type($method), $reference, $method->is_static(), $deprecation);
    }
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public static function from_name(string $class_name, string $method_name): self
    {
        return new self($class_name, $method_name, 'public', '', '', [], 0, new Unknown_Type(), '', false, null);
    }
    /**
     * @param class-string      $className
     * @param non-empty-string  $methodName
     * @param array<int, mixed> $defaultParameterValues
     * @param non-negative-int  $numberOfParameters
     */
    private function __construct(private readonly string $class_name, private readonly string $method_name, private readonly string $modifier, private readonly string $arguments_for_declaration, private readonly string $arguments_for_call, private readonly array $default_parameter_values, private readonly int $number_of_parameters, Type $return_type, private readonly string $reference, private readonly bool $static, private readonly ?string $deprecation)
    {
        $this->return_type = $return_type;
    }
    /**
     * @return non-empty-string
     */
    public function method_name(): string
    {
        return $this->method_name;
    }
    /**
     * @throws RuntimeException
     */
    public function generate_code(): string
    {
        if ($this->static) {
            $template_file = 'doubled_static_method.tpl';
        } else {
            $template_file = 'doubled_method.tpl';
        }
        $deprecation = $this->deprecation;
        $return_result = '';
        if (!$this->return_type->is_never() && !$this->return_type->is_void()) {
            $return_result = <<<'EOT'
            
            
                    return $__phpunit_result;
            EOT;
        }
        if (null !== $this->deprecation) {
            $deprecation = "The {$this->class_name}::{$this->method_name} method is deprecated ({$this->deprecation}).";
            $deprecation_template = $this->load_template('deprecation.tpl');
            $deprecation_template->set_var(['deprecation' => var_export($deprecation, true)]);
            $deprecation = $deprecation_template->render();
        }
        $template = $this->load_template($template_file);
        $arguments_count = 0;
        if (str_contains($this->arguments_for_call, '...')) {
            $arguments_count = null;
        } elseif ($this->arguments_for_call !== '') {
            $arguments_count = substr_count($this->arguments_for_call, ',') + 1;
        }
        $return_declaration = '';
        $return_type_as_string = $this->return_type->as_string();
        if ($return_type_as_string !== '') {
            $return_declaration = ': ' . $return_type_as_string;
        }
        $template->set_var(['arguments_decl' => $this->arguments_for_declaration, 'arguments_call' => $this->arguments_for_call, 'return_declaration' => $return_declaration, 'return_type' => $this->return_type->as_string(), 'arguments_count' => (string) $arguments_count, 'class_name' => $this->class_name, 'method_name' => $this->method_name, 'modifier' => $this->modifier, 'reference' => $this->reference, 'deprecation' => $deprecation, 'return_result' => $return_result]);
        return $template->render();
    }
    public function return_type(): Type
    {
        return $this->return_type;
    }
    /**
     * @return array<int, mixed>
     */
    public function default_parameter_values(): array
    {
        return $this->default_parameter_values;
    }
    /**
     * @return non-negative-int
     */
    public function number_of_parameters(): int
    {
        return $this->number_of_parameters;
    }
    /**
     * Returns the parameters of a function or method.
     *
     * @throws RuntimeException
     */
    private static function method_parameters_for_declaration(ReflectionMethod $method): string
    {
        $parameters = [];
        $types = (new Reflection_Mapper())->from_parameter_types($method);
        foreach ($method->get_parameters() as $i => $parameter) {
            $name = '$' . $parameter->get_name();
            /* Note: PHP extensions may use empty names for reference arguments
             * or "..." for methods taking a variable number of arguments.
             */
            // @codeCoverageIgnoreStart
            if ($name === '$' || $name === '$...') {
                $name = '$arg' . $i;
            }
            // @codeCoverageIgnoreEnd
            $default = '';
            $reference = '';
            $type_declaration = '';
            assert(array_key_exists($i, $types));
            if (!$types[$i]->type()->is_unknown()) {
                $type_declaration = $types[$i]->type()->as_string() . ' ';
            }
            if ($parameter->is_passed_by_reference()) {
                $reference = '&';
            }
            if ($parameter->is_variadic()) {
                $name = '...' . $name;
            } elseif ($parameter->is_default_value_available()) {
                $default = ' = ' . self::export_default_value($parameter);
            } elseif ($parameter->is_optional()) {
                $default = ' = null';
            }
            $parameters[] = $type_declaration . $reference . $name . $default;
        }
        return implode(', ', $parameters);
    }
    /**
     * Returns the parameters of a function or method.
     *
     * @throws ReflectionException
     */
    private static function method_parameters_for_call(ReflectionMethod $method): string
    {
        $parameters = [];
        foreach ($method->get_parameters() as $i => $parameter) {
            $name = '$' . $parameter->get_name();
            /* Note: PHP extensions may use empty names for reference arguments
             * or "..." for methods taking a variable number of arguments.
             */
            // @codeCoverageIgnoreStart
            if ($name === '$' || $name === '$...') {
                $name = '$arg' . $i;
            }
            // @codeCoverageIgnoreEnd
            if ($parameter->is_variadic()) {
                continue;
            }
            if ($parameter->is_passed_by_reference()) {
                $parameters[] = '&' . $name;
            } else {
                $parameters[] = $name;
            }
        }
        return implode(', ', $parameters);
    }
    /**
     * @throws ReflectionException
     */
    private static function export_default_value(ReflectionParameter $parameter): string
    {
        try {
            $default_value = $parameter->get_default_value();
            if (!is_object($default_value)) {
                return var_export($default_value, true);
            }
            $parameter_as_string = $parameter->__toString();
            return explode(' = ', substr(substr($parameter_as_string, strpos($parameter_as_string, '<optional> ') + strlen('<optional> ')), 0, -2))[1];
            // @codeCoverageIgnoreStart
        } catch (\Reflection_Exception $e) {
            throw new Reflection_Exception($e->get_message(), $e->get_code(), $e);
        }
        // @codeCoverageIgnoreEnd
    }
    /**
     * @return array<int, mixed>
     */
    private static function method_parameters_default_values(ReflectionMethod $method): array
    {
        $result = [];
        foreach ($method->get_parameters() as $i => $parameter) {
            if (!$parameter->is_default_value_available()) {
                continue;
            }
            $result[$i] = $parameter->get_default_value();
        }
        return $result;
    }
}