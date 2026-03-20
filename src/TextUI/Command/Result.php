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
namespace Php_Unit\Text_Ui\Command;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Result
{
    public const int SUCCESS = 0;
    public const int FAILURE = 1;
    public const int EXCEPTION = 2;
    public const int CRASH = 255;
    public static function from(string $output = '', int $shell_exit_code = self::SUCCESS): self
    {
        return new self($output, $shell_exit_code);
    }
    private function __construct(private string $output, private int $shell_exit_code)
    {
    }
    public function output(): string
    {
        return $this->output;
    }
    public function shell_exit_code(): int
    {
        return $this->shell_exit_code;
    }
}