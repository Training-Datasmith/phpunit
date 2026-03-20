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
namespace Php_Unit\Text_Ui\Xml_Configuration;

use Php_Unit\Text_Ui\Configuration\Group_Collection;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Groups
{
    public function __construct(private Group_Collection $include, private Group_Collection $exclude)
    {
    }
    public function has_include(): bool
    {
        return !$this->include->is_empty();
    }
    public function include(): Group_Collection
    {
        return $this->include;
    }
    public function has_exclude(): bool
    {
        return !$this->exclude->is_empty();
    }
    public function exclude(): Group_Collection
    {
        return $this->exclude;
    }
}