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
namespace Php_Unit\Framework\Test_Status;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract readonly class Test_Status
{
    public static function from(int $status): self
    {
        return match ($status) {
            0 => self::success(),
            1 => self::skipped(),
            2 => self::incomplete(),
            3 => self::notice(),
            4 => self::deprecation(),
            5 => self::risky(),
            6 => self::warning(),
            7 => self::failure(),
            8 => self::error(),
            default => self::unknown(),
        };
    }
    public static function unknown(): self
    {
        return new Unknown();
    }
    public static function success(): self
    {
        return new Success();
    }
    public static function skipped(string $message = ''): self
    {
        return new Skipped($message);
    }
    public static function incomplete(string $message = ''): self
    {
        return new Incomplete($message);
    }
    public static function notice(string $message = ''): self
    {
        return new Notice($message);
    }
    public static function deprecation(string $message = ''): self
    {
        return new Deprecation($message);
    }
    public static function failure(string $message = ''): self
    {
        return new Failure($message);
    }
    public static function error(string $message = ''): self
    {
        return new Error($message);
    }
    public static function warning(string $message = ''): self
    {
        return new Warning($message);
    }
    public static function risky(string $message = ''): self
    {
        return new Risky($message);
    }
    private function __construct(private string $message = '')
    {
    }
    /**
     * @phpstan-assert-if-true Known $this
     */
    public function is_known(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true Unknown $this
     */
    public function is_unknown(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true Success $this
     */
    public function is_success(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true Skipped $this
     */
    public function is_skipped(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true Incomplete $this
     */
    public function is_incomplete(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true Notice $this
     */
    public function is_notice(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true Deprecation $this
     */
    public function is_deprecation(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true Failure $this
     */
    public function is_failure(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true Error $this
     */
    public function is_error(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true Warning $this
     */
    public function is_warning(): bool
    {
        return false;
    }
    /**
     * @phpstan-assert-if-true Risky $this
     */
    public function is_risky(): bool
    {
        return false;
    }
    public function message(): string
    {
        return $this->message;
    }
    public function is_more_important_than(self $other): bool
    {
        return $this->as_int() > $other->as_int();
    }
    abstract public function as_int(): int;
    abstract public function as_string(): string;
}