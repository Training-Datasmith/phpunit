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
use Php_Unit\Framework\Execution_Order_Dependency;
use Php_Unit\Metadata\Depends_On_Class;
use Php_Unit\Metadata\Depends_On_Method;
use Php_Unit\Metadata\Parser\Registry;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Dependencies
{
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     *
     * @return list<ExecutionOrderDependency>
     */
    public static function dependencies(string $class_name, string $method_name): array
    {
        $dependencies = [];
        foreach (Registry::parser()->for_class_and_method($class_name, $method_name)->is_depends() as $metadata) {
            if ($metadata->is_depends_on_class()) {
                assert($metadata instanceof Depends_On_Class);
                $dependencies[] = Execution_Order_Dependency::for_class($metadata);
                continue;
            }
            assert($metadata instanceof Depends_On_Method);
            if ($metadata->method_name() === '') {
                $dependencies[] = Execution_Order_Dependency::invalid();
                continue;
            }
            $dependencies[] = Execution_Order_Dependency::for_method($metadata);
        }
        return $dependencies;
    }
}