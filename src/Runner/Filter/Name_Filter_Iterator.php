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
namespace Php_Unit\Runner\Filter;

use function end;
use Php_Unit\Framework\Test;
use Php_Unit\Framework\Test_Suite;
use Php_Unit\Runner\Phpt\Test_Case as PhptTestCase;
use function preg_match;
use Recursive_Filter_Iterator;
use Recursive_Iterator;
use function sprintf;
use function substr;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class Name_Filter_Iterator extends Recursive_Filter_Iterator
{
    /**
     * @var non-empty-string
     */
    private readonly string $regular_expression;
    private readonly ?int $data_set_minimum;
    private readonly ?int $data_set_maximum;
    /**
     * @param RecursiveIterator<int, Test> $iterator
     * @param non-empty-string             $filter
     */
    public function __construct(Recursive_Iterator $iterator, string $filter)
    {
        parent::__construct($iterator);
        $prepared_filter = $this->prepare_filter($filter);
        $this->regular_expression = $prepared_filter['regularExpression'];
        $this->data_set_minimum = $prepared_filter['dataSetMinimum'];
        $this->data_set_maximum = $prepared_filter['dataSetMaximum'];
    }
    public function accept(): bool
    {
        $test = $this->get_inner_iterator()->current();
        if ($test instanceof Test_Suite) {
            return true;
        }
        if ($test instanceof Phpt_Test_Case) {
            return false;
        }
        $name = $test::class . '::' . $test->name_with_data_set();
        $accepted = @preg_match($this->regular_expression, $name, $matches) === 1;
        if ($accepted && isset($this->data_set_maximum)) {
            $set = end($matches);
            $accepted = $set >= $this->data_set_minimum && $set <= $this->data_set_maximum;
        }
        return $this->do_accept($accepted);
    }
    abstract protected function do_accept(bool $result): bool;
    /**
     * @param non-empty-string $filter
     *
     * @return array{regularExpression: non-empty-string, dataSetMinimum: ?int, dataSetMaximum: ?int}
     */
    private function prepare_filter(string $filter): array
    {
        $data_set_minimum = null;
        $data_set_maximum = null;
        if (preg_match('/[a-zA-Z0-9]/', substr($filter, 0, 1)) === 1 || @preg_match($filter, '') === false) {
            // Handles:
            //  * testAssertEqualsSucceeds#4
            //  * testAssertEqualsSucceeds#4-8
            if (preg_match('/^(.*?)#(\d+)(?:-(\d+))?$/', $filter, $matches)) {
                if (isset($matches[3]) && $matches[2] < $matches[3]) {
                    $filter = sprintf('%s.*with data set #(\d+)$', $matches[1]);
                    $data_set_minimum = (int) $matches[2];
                    $data_set_maximum = (int) $matches[3];
                } else {
                    $filter = sprintf('%s.*with data set #%s$', $matches[1], $matches[2]);
                }
            } elseif (preg_match('/^(.*?)@(.+)$/', $filter, $matches)) {
                $filter = sprintf('%s.*with data set "%s"$', $matches[1], $matches[2]);
            }
            // Do NOT use preg_quote, to keep magic characters.
            $filter = sprintf('{%s}i', $filter);
        }
        return ['regularExpression' => $filter, 'dataSetMinimum' => $data_set_minimum, 'dataSetMaximum' => $data_set_maximum];
    }
}