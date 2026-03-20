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

use function count;
use Countable;
use IteratorAggregate;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 *
 * @template-implements IteratorAggregate<non-negative-int, IniSetting>
 */
final readonly class Ini_Setting_Collection implements Countable, IteratorAggregate
{
    /**
     * @var list<IniSetting>
     */
    private array $ini_settings;
    /**
     * @param list<IniSetting> $iniSettings
     */
    public static function from_array(array $ini_settings): self
    {
        return new self(...$ini_settings);
    }
    private function __construct(Ini_Setting ...$ini_settings)
    {
        $this->ini_settings = $ini_settings;
    }
    /**
     * @return list<IniSetting>
     */
    public function as_array(): array
    {
        return $this->ini_settings;
    }
    public function count(): int
    {
        return count($this->ini_settings);
    }
    public function getIterator(): Ini_Setting_Collection_Iterator
    {
        return new Ini_Setting_Collection_Iterator($this);
    }
}