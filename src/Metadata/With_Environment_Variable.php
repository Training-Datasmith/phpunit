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
final readonly class With_Environment_Variable extends Metadata
{
    /**
     * @param non-empty-string $environmentVariableName
     */
    protected function __construct(Level $level, private string $environment_variable_name, private null|string $value)
    {
        parent::__construct($level);
    }
    public function is_with_environment_variable(): true
    {
        return true;
    }
    /**
     * @return non-empty-string
     */
    public function environment_variable_name(): string
    {
        return $this->environment_variable_name;
    }
    public function value(): null|string
    {
        return $this->value;
    }
}