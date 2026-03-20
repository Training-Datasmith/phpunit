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

use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Hooked_Property_Generator
{
    /**
     * @param class-string         $className
     * @param list<HookedProperty> $properties
     */
    public function generate(string $class_name, array $properties): string
    {
        $code = '';
        foreach ($properties as $property) {
            $code .= sprintf(<<<'EOT'
            
                public %s $%s {
            EOT, $property->type()->as_string(), $property->name());
            if ($property->has_get_hook()) {
                $code .= sprintf(<<<'EOT'
                
                        get {
                            return $this->__phpunit_getInvocationHandler()->invoke(
                                new \PHPUnit\Framework\MockObject\Invocation(
                                    '%s', '$%s::get', [], '%s', $this
                                )
                            );
                        }
                
                EOT, $class_name, $property->name(), $property->type()->as_string());
            }
            if ($property->has_set_hook()) {
                $code .= sprintf(<<<'EOT'
                
                        set (%s $value) {
                            $this->__phpunit_getInvocationHandler()->invoke(
                                new \PHPUnit\Framework\MockObject\Invocation(
                                    '%s', '$%s::set', [$value], 'void', $this
                                )
                            );
                        }
                
                EOT, $property->setter_type()->as_string(), $class_name, $property->name());
            }
            $code .= <<<'EOT'
                }
            
            EOT;
        }
        return $code;
    }
}