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
namespace Php_Unit\Event\Code;

use const PHP_EOL;
use Php_Unit\Event\No_Previous_Throwable_Exception;
/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Throwable
{
    /**
     * @param class-string $className
     */
    public function __construct(private string $class_name, private string $message, private string $description, private string $stack_trace, private ?Throwable $previous)
    {
    }
    /**
     * @throws NoPreviousThrowableException
     */
    public function as_string(): string
    {
        $buffer = $this->description();
        if ($this->stack_trace() !== '') {
            $buffer .= PHP_EOL . $this->stack_trace();
        }
        if ($this->has_previous()) {
            $buffer .= PHP_EOL . 'Caused by' . PHP_EOL . $this->previous()->as_string();
        }
        return $buffer;
    }
    /**
     * @return class-string
     */
    public function class_name(): string
    {
        return $this->class_name;
    }
    public function message(): string
    {
        return $this->message;
    }
    public function description(): string
    {
        return $this->description;
    }
    public function stack_trace(): string
    {
        return $this->stack_trace;
    }
    /**
     * @phpstan-assert-if-true !null $this->previous
     */
    public function has_previous(): bool
    {
        return $this->previous !== null;
    }
    /**
     * @throws NoPreviousThrowableException
     */
    public function previous(): self
    {
        if ($this->previous === null) {
            throw new No_Previous_Throwable_Exception();
        }
        return $this->previous;
    }
}