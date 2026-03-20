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

use function assert;
use Php_Unit\Event\Code\Test_Method_Builder;
use Php_Unit\Event\Code\Throwable_Builder;
use Php_Unit\Event\Emitter;
use Php_Unit\Event\Facade;
use Php_Unit\Runner\Code_Coverage;
use Php_Unit\Test_Runner\Test_Result\Passed_Tests;
use function trim;
use function unserialize;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Child_Process_Result_Processor
{
    public function __construct(private Facade $event_facade, private Emitter $emitter, private Passed_Tests $passed_tests, private Code_Coverage $code_coverage)
    {
    }
    public function process(Test $test, string $serialized_process_result, string $stderr): void
    {
        if ($stderr !== '') {
            $exception = new Exception(trim($stderr));
            assert($test instanceof Test_Case);
            $this->emitter->test_errored(Test_Method_Builder::from_test_case($test), Throwable_Builder::from($exception));
            return;
        }
        $child_result = @unserialize($serialized_process_result, ['allowed_classes' => true]);
        if ($child_result === false) {
            $this->emitter->child_process_errored();
            $exception = new Assertion_Failed_Error('Test was run in child process and ended unexpectedly');
            assert($test instanceof Test_Case);
            $this->emitter->test_errored(Test_Method_Builder::from_test_case($test), Throwable_Builder::from($exception));
            $this->emitter->test_finished(Test_Method_Builder::from_test_case($test), 0);
            return;
        }
        $this->event_facade->forward($child_result->events);
        $this->passed_tests->import($child_result->passed_tests);
        assert($test instanceof Test_Case);
        $test->set_result($child_result->test_result);
        $test->add_to_assertion_count($child_result->num_assertions);
        if (!$this->code_coverage->is_active()) {
            return;
        }
        // @codeCoverageIgnoreStart
        if (!$child_result->code_coverage instanceof \Sebastian_Bergmann\Code_Coverage\Code_Coverage) {
            return;
        }
        Code_Coverage::instance()->code_coverage()->merge($child_result->code_coverage);
        // @codeCoverageIgnoreEnd
    }
}