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
    /**
     * Creates a TestStatus instance from its integer representation.
     *
     * The integer codes are used for serialisation (e.g. when passing test
     * results across process boundaries). Unknown codes map to {@see Unknown}.
     *
     * @param int $status Integer status code (0–8; see constants in subclasses)
     *
     * @return self The corresponding status instance
     */
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
    /**
     * Creates a status representing a test that has not yet been run.
     */
    public static function unknown(): self
    {
        return new Unknown();
    }
    /**
     * Creates a status representing a test that passed all assertions.
     */
    public static function success(): self
    {
        return new Success();
    }
    /**
     * Creates a status representing a test that was skipped (via markTestSkipped()).
     *
     * @param string $message Human-readable reason for skipping
     */
    public static function skipped(string $message = ''): self
    {
        return new Skipped($message);
    }
    /**
     * Creates a status representing a test that was marked as incomplete.
     *
     * @param string $message Human-readable description of what is missing
     */
    public static function incomplete(string $message = ''): self
    {
        return new Incomplete($message);
    }
    /**
     * Creates a status representing a test that triggered a PHP notice.
     *
     * @param string $message The notice message
     */
    public static function notice(string $message = ''): self
    {
        return new Notice($message);
    }
    /**
     * Creates a status representing a test that triggered a deprecation notice.
     *
     * @param string $message The deprecation message
     */
    public static function deprecation(string $message = ''): self
    {
        return new Deprecation($message);
    }
    /**
     * Creates a status representing a test that failed an assertion.
     *
     * @param string $message The assertion failure message
     */
    public static function failure(string $message = ''): self
    {
        return new Failure($message);
    }
    /**
     * Creates a status representing a test that threw an unexpected exception or PHP error.
     *
     * @param string $message The error message
     */
    public static function error(string $message = ''): self
    {
        return new Error($message);
    }
    /**
     * Creates a status representing a test that produced a PHP warning.
     *
     * @param string $message The warning message
     */
    public static function warning(string $message = ''): self
    {
        return new Warning($message);
    }
    /**
     * Creates a status representing a test that was flagged as risky.
     *
     * Tests are risky when they perform no assertions, perform unexpected output,
     * or modify global/static state without proper backup.
     *
     * @param string $message Human-readable description of why the test is risky
     */
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