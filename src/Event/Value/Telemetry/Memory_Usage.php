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
namespace Php_Unit\Event\Telemetry;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Memory_Usage
{
    public static function from_bytes(int $bytes): self
    {
        return new self($bytes);
    }
    private function __construct(private int $bytes)
    {
    }
    public function bytes(): int
    {
        return $this->bytes;
    }
    public function diff(self $other): self
    {
        return self::from_bytes($this->bytes - $other->bytes);
    }
}