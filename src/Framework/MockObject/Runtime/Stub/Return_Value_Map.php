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
namespace Php_Unit\Framework\Mock_Object\Stub;

use function array_pop;
use function count;
use function is_array;
use Php_Unit\Framework\Mock_Object\Invocation;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Return_Value_Map implements Stub
{
    /**
     * @param array<mixed> $valueMap
     */
    public function __construct(private array $value_map)
    {
    }
    public function invoke(Invocation $invocation): mixed
    {
        $parameter_count = count($invocation->parameters());
        foreach ($this->value_map as $map) {
            if (!is_array($map)) {
                continue;
            }
            if ($parameter_count !== count($map) - 1) {
                continue;
            }
            $return = array_pop($map);
            if ($invocation->parameters() === $map) {
                return $return;
            }
        }
        return null;
    }
}