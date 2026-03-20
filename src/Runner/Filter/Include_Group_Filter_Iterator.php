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
namespace Php_Unit\Runner\Filter;

use function in_array;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Include_Group_Filter_Iterator extends Group_Filter_Iterator
{
    /**
     * @param non-empty-string       $id
     * @param list<non-empty-string> $groupTests
     */
    protected function do_accept(string $id, array $group_tests): bool
    {
        return in_array($id, $group_tests, true);
    }
}