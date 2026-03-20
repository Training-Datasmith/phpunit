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
namespace Php_Unit\Framework;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Phpt_Assertion_Failed_Error extends Assertion_Failed_Error
{
    /**
     * @param list<array{file: string, line: int, function: string, type: string}> $syntheticTrace
     */
    public function __construct(string $message, int $code, private readonly string $synthetic_file, private readonly int $synthetic_line, private readonly array $synthetic_trace, private readonly string $diff)
    {
        parent::__construct($message, $code);
    }
    public function synthetic_file(): string
    {
        return $this->synthetic_file;
    }
    public function synthetic_line(): int
    {
        return $this->synthetic_line;
    }
    /**
     * @return list<array{file: string, line: int, function: string, type: string}>
     */
    public function synthetic_trace(): array
    {
        return $this->synthetic_trace;
    }
    public function diff(): string
    {
        return $this->diff;
    }
}