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
namespace Php_Unit\Text_Ui\Configuration;

use Iterator;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @template-implements Iterator<non-negative-int, IniSetting>
 */
final class Ini_Setting_Collection_Iterator implements Iterator
{
    /**
     * @var list<IniSetting>
     */
    private readonly array $ini_settings;
    /**
     * @var non-negative-int
     */
    private int $position = 0;
    public function __construct(Ini_Setting_Collection $ini_settings)
    {
        $this->ini_settings = $ini_settings->as_array();
    }
    public function rewind(): void
    {
        $this->position = 0;
    }
    public function valid(): bool
    {
        return isset($this->ini_settings[$this->position]);
    }
    /**
     * @return non-negative-int
     */
    public function key(): int
    {
        return $this->position;
    }
    public function current(): Ini_Setting
    {
        return $this->ini_settings[$this->position];
    }
    public function next(): void
    {
        $this->position++;
    }
}