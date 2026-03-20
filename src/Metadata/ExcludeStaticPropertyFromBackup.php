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
namespace Php_Unit\Metadata;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Exclude_Static_Property_From_Backup extends Metadata
{
    /**
     * @param class-string     $className
     * @param non-empty-string $propertyName
     */
    protected function __construct(Level $level, private string $class_name, private string $property_name)
    {
        parent::__construct($level);
    }
    public function is_exclude_static_property_from_backup(): true
    {
        return true;
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
    public function property_name(): string
    {
        return $this->property_name;
    }
}