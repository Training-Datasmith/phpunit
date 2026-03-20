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

use function in_array;
use Php_Unit\Event\Test_Data\No_Data_Set_From_Data_Provider_Exception;
use Php_Unit\Framework\Test;
use Php_Unit\Framework\Test_Case;
use Php_Unit\Framework\Test_Suite;
use Php_Unit\Runner\Phpt\Test_Case as PhptTestCase;
use Recursive_Filter_Iterator;
use Recursive_Iterator;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Test_Id_Filter_Iterator extends Recursive_Filter_Iterator
{
    /**
     * @param RecursiveIterator<int, Test>     $iterator
     * @param non-empty-list<non-empty-string> $testIds
     */
    public function __construct(Recursive_Iterator $iterator, private readonly array $test_ids)
    {
        parent::__construct($iterator);
    }
    public function accept(): bool
    {
        $test = $this->get_inner_iterator()->current();
        if ($test instanceof Test_Suite) {
            return true;
        }
        if (!$test instanceof Test_Case && !$test instanceof Phpt_Test_Case) {
            return false;
        }
        try {
            return in_array($test->value_object_for_events()->id(), $this->test_ids, true);
        } catch (No_Data_Set_From_Data_Provider_Exception) {
            return false;
        }
    }
}