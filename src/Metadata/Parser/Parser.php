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

use Php_Unit\Metadata\Metadata_Collection;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This interface is not covered by the backward compatibility promise for PHPUnit
 */
interface Parser
{
    /**
     * @param class-string $className
     */
    public function for_class(string $class_name): Metadata_Collection;
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public function for_method(string $class_name, string $method_name): Metadata_Collection;
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public function for_class_and_method(string $class_name, string $method_name): Metadata_Collection;
}