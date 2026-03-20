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
namespace Php_Unit\Test_Runner\Test_Result\Issues;

use function array_keys;
use function count;
use Php_Unit\Event\Code\Test;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Issue
{
    /**
     * @var non-empty-array<non-empty-string, array{test: Test, count: int}>
     */
    private array $triggering_tests;
    /**
     * @param non-empty-string $file
     * @param positive-int     $line
     * @param non-empty-string $description
     */
    public static function from(string $file, int $line, string $description, Test $triggering_test, ?string $stack_trace = null): self
    {
        return new self($file, $line, $description, $triggering_test, $stack_trace);
    }
    /**
     * @param non-empty-string $file
     * @param positive-int     $line
     * @param non-empty-string $description
     */
    private function __construct(private readonly string $file, private readonly int $line, private readonly string $description, Test $triggering_test, private readonly ?string $stack_trace)
    {
        $this->triggering_tests = [$triggering_test->id() => ['test' => $triggering_test, 'count' => 1]];
    }
    public function triggered_by(Test $test): void
    {
        if (isset($this->triggering_tests[$test->id()])) {
            $this->triggering_tests[$test->id()]['count']++;
            return;
        }
        $this->triggering_tests[$test->id()] = ['test' => $test, 'count' => 1];
    }
    /**
     * @return non-empty-string
     */
    public function file(): string
    {
        return $this->file;
    }
    /**
     * @return positive-int
     */
    public function line(): int
    {
        return $this->line;
    }
    /**
     * @return non-empty-string
     */
    public function description(): string
    {
        return $this->description;
    }
    /**
     * @return non-empty-array<non-empty-string, array{test: Test, count: int}>
     */
    public function triggering_tests(): array
    {
        return $this->triggering_tests;
    }
    /**
     * @phpstan-assert-if-true !null $this->stackTrace
     */
    public function has_stack_trace(): bool
    {
        return $this->stack_trace !== null;
    }
    /**
     * @return ?non-empty-string
     */
    public function stack_trace(): ?string
    {
        return $this->stack_trace;
    }
    public function triggered_in_test(): bool
    {
        return count($this->triggering_tests) === 1 && $this->file === $this->triggering_tests[array_keys($this->triggering_tests)[0]]['test']->file();
    }
}