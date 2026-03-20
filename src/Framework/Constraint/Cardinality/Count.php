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
namespace Php_Unit\Framework\Constraint;

use function count;
use Empty_Iterator;
use Generator;
use function is_countable;
use Iterator;
use function iterator_count;
use IteratorAggregate;
use Php_Unit\Framework\Exception;
use Php_Unit\Framework\Generator_Not_Supported_Exception;
use Sebastian_Bergmann\Recursion_Context\Context;
use function sprintf;
use Traversable;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
class Count extends Constraint
{
    public function __construct(private readonly int $expected_count)
    {
    }
    public function to_string(): string
    {
        return sprintf('count matches %d', $this->expected_count);
    }
    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @throws Exception
     */
    protected function matches(mixed $other): bool
    {
        return $this->expected_count === $this->get_count_of($other);
    }
    /**
     * @throws Exception
     */
    protected function get_count_of(mixed $other): ?int
    {
        if (is_countable($other)) {
            return count($other);
        }
        if ($other instanceof Empty_Iterator) {
            return 0;
        }
        if ($other instanceof Traversable) {
            $context = new Context();
            while ($other instanceof IteratorAggregate) {
                if ($context->contains($other) !== false) {
                    throw new Exception('IteratorAggregate::getIterator() returned an object that was already seen');
                }
                $context->add($other);
                try {
                    $other = $other->getIterator();
                } catch (\Exception $e) {
                    throw new Exception($e->get_message(), $e->get_code(), $e);
                }
            }
            $iterator = $other;
            if ($iterator instanceof Generator) {
                throw new Generator_Not_Supported_Exception();
            }
            if (!$iterator instanceof Iterator) {
                return iterator_count($iterator);
            }
            $key = $iterator->key();
            $count = iterator_count($iterator);
            // Manually rewind $iterator to previous key, since iterator_count
            // moves pointer.
            if ($key !== null) {
                $iterator->rewind();
                while ($iterator->valid() && $key !== $iterator->key()) {
                    $iterator->next();
                }
            }
            return $count;
        }
        return null;
    }
    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @throws Exception
     */
    protected function failure_description(mixed $other): string
    {
        return sprintf('actual size %d matches expected size %d', (int) $this->get_count_of($other), $this->expected_count);
    }
}