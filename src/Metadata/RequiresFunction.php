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
final readonly class Requires_Function extends Metadata
{
    /**
     * @param non-empty-string $functionName
     */
    protected function __construct(Level $level, private string $function_name)
    {
        parent::__construct($level);
    }
    public function is_requires_function(): true
    {
        return true;
    }
    /**
     * @return non-empty-string
     */
    public function function_name(): string
    {
        return $this->function_name;
    }
}