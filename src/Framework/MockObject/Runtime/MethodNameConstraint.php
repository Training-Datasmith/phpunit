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

use Php_Unit\Framework\Constraint\Constraint;
use function sprintf;
use function strtolower;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Method_Name_Constraint extends Constraint
{
    public function __construct(private readonly string $method_name)
    {
    }
    public function method_name(): string
    {
        return $this->method_name;
    }
    public function to_string(): string
    {
        return sprintf('is "%s"', $this->method_name);
    }
    protected function matches(mixed $other): bool
    {
        return strtolower($this->method_name) === strtolower((string) $other);
    }
}