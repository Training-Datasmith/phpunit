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
 * @template-implements IteratorAggregate<non-negative-int, Variable>
 */
final readonly class Variable_Collection implements Countable, IteratorAggregate
{
    /**
     * @var list<Variable>
     */
    private array $variables;
    /**
     * @param list<Variable> $variables
     */
    public static function from_array(array $variables): self
    {
        return new self(...$variables);
    }
    private function __construct(Variable ...$variables)
    {
        $this->variables = $variables;
    }
    /**
     * @return list<Variable>
     */
    public function as_array(): array
    {
        return $this->variables;
    }
    public function count(): int
    {
        return count($this->variables);
    }
    public function getIterator(): Variable_Collection_Iterator
    {
        return new Variable_Collection_Iterator($this);
    }
}