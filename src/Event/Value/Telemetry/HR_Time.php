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

use Php_Unit\Event\InvalidArgumentException;
use function sprintf;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Hr_Time
{
    private int $seconds;
    private int $nanoseconds;
    /**
     * @throws InvalidArgumentException
     */
    public static function from_seconds_and_nanoseconds(int $seconds, int $nanoseconds): self
    {
        return new self($seconds, $nanoseconds);
    }
    /**
     * @throws InvalidArgumentException
     */
    private function __construct(int $seconds, int $nanoseconds)
    {
        $this->ensure_not_negative($seconds, 'seconds');
        $this->ensure_not_negative($nanoseconds, 'nanoseconds');
        $this->ensure_nano_seconds_in_range($nanoseconds);
        $this->seconds = $seconds;
        $this->nanoseconds = $nanoseconds;
    }
    public function seconds(): int
    {
        return $this->seconds;
    }
    public function nanoseconds(): int
    {
        return $this->nanoseconds;
    }
    public function duration(self $start): Duration
    {
        $seconds = $this->seconds - $start->seconds();
        $nanoseconds = $this->nanoseconds - $start->nanoseconds();
        if ($nanoseconds < 0) {
            $seconds--;
            $nanoseconds += 1000000000;
        }
        if ($seconds < 0) {
            return Duration::from_seconds_and_nanoseconds(0, 0);
        }
        return Duration::from_seconds_and_nanoseconds($seconds, $nanoseconds);
    }
    /**
     * @throws InvalidArgumentException
     */
    private function ensure_not_negative(int $value, string $type): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException(sprintf('Value for %s must not be negative.', $type));
        }
    }
    /**
     * @throws InvalidArgumentException
     */
    private function ensure_nano_seconds_in_range(int $nanoseconds): void
    {
        if ($nanoseconds > 999999999) {
            throw new InvalidArgumentException('Value for nanoseconds must not be greater than 999999999.');
        }
    }
}