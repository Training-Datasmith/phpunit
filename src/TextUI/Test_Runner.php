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
namespace Php_Unit\Text_Ui;

use function mt_srand;
use Php_Unit\Event;
use Php_Unit\Framework\Test_Suite;
use Php_Unit\Runner\Result_Cache\Result_Cache;
use Php_Unit\Runner\Test_Suite_Sorter;
use Php_Unit\Text_Ui\Configuration\Configuration;
use Throwable;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Test_Runner
{
    /**
     * @throws RuntimeException
     */
    public function run(Configuration $configuration, Result_Cache $result_cache, Test_Suite $suite): void
    {
        try {
            Event\Facade::emitter()->test_runner_started();
            if ($configuration->execution_order() === Test_Suite_Sorter::ORDER_RANDOMIZED) {
                mt_srand($configuration->random_order_seed());
            }
            if ($configuration->execution_order() !== Test_Suite_Sorter::ORDER_DEFAULT || $configuration->execution_order_defects() !== Test_Suite_Sorter::ORDER_DEFAULT || $configuration->resolve_dependencies()) {
                $result_cache->load();
                (new Test_Suite_Sorter($result_cache))->reorder_tests_in_suite($suite, $configuration->execution_order(), $configuration->resolve_dependencies(), $configuration->execution_order_defects());
                Event\Facade::emitter()->test_suite_sorted($configuration->execution_order(), $configuration->execution_order_defects(), $configuration->resolve_dependencies());
            }
            (new Test_Suite_Filter_Processor())->process($configuration, $suite);
            Event\Facade::emitter()->test_runner_execution_started(Event\Test_Suite\Test_Suite_Builder::from($suite));
            $suite->run();
            Event\Facade::emitter()->test_runner_execution_finished();
            Event\Facade::emitter()->test_runner_finished();
        } catch (Throwable $t) {
            throw new RuntimeException($t->get_message(), (int) $t->get_code(), $t);
        }
    }
}