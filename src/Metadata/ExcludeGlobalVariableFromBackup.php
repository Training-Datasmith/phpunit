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
final readonly class Exclude_Global_Variable_From_Backup extends Metadata
{
    /**
     * @param non-empty-string $globalVariableName
     */
    protected function __construct(Level $level, private string $global_variable_name)
    {
        parent::__construct($level);
    }
    public function is_exclude_global_variable_from_backup(): true
    {
        return true;
    }
    /**
     * @return non-empty-string
     */
    public function global_variable_name(): string
    {
        return $this->global_variable_name;
    }
}