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
namespace Php_Unit\Logging\Team_City;

use function assert;
use function getmypid;
use function ini_get;
use function is_a;
use Php_Unit\Event\Code\Test_Method;
use Php_Unit\Event\Code\Throwable;
use Php_Unit\Event\Event;
use Php_Unit\Event\Facade;
use Php_Unit\Event\InvalidArgumentException;
use Php_Unit\Event\Telemetry\Hr_Time;
use Php_Unit\Event\Test\Before_First_Test_Method_Errored;
use Php_Unit\Event\Test\Considered_Risky;
use Php_Unit\Event\Test\Errored;
use Php_Unit\Event\Test\Failed;
use Php_Unit\Event\Test\Finished;
use Php_Unit\Event\Test\Marked_Incomplete;
use Php_Unit\Event\Test\Prepared;
use Php_Unit\Event\Test\Skipped;
use Php_Unit\Event\Test_Suite\Finished as TestSuiteFinished;
use Php_Unit\Event\Test_Suite\Skipped as TestSuiteSkipped;
use Php_Unit\Event\Test_Suite\Started as TestSuiteStarted;
use Php_Unit\Event\Test_Suite\Test_Suite_For_Test_Class;
use Php_Unit\Event\Test_Suite\Test_Suite_For_Test_Method_With_Data_Provider;
use Php_Unit\Framework\Exception as FrameworkException;
use Php_Unit\Text_Ui\Output\Printer;
use function round;
use function sprintf;
use function str_replace;
use function stripos;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Team_City_Logger
{
    private bool $is_summary_test_count_printed = false;
    private ?Hr_Time $time = null;
    private ?int $flow_id = null;
    public function __construct(private readonly Printer $printer, Facade $facade)
    {
        $this->register_subscribers($facade);
        $this->set_flow_id();
    }
    public function test_suite_started(Test_Suite_Started $event): void
    {
        $test_suite = $event->test_suite();
        if (!$this->is_summary_test_count_printed) {
            $this->is_summary_test_count_printed = true;
            $this->write_message('testCount', ['count' => $test_suite->count()]);
        }
        $parameters = ['name' => $test_suite->name()];
        if ($test_suite->is_for_test_class()) {
            assert($test_suite instanceof Test_Suite_For_Test_Class);
            $parameters['locationHint'] = sprintf('php_qn://%s::\%s', $test_suite->file(), $test_suite->name());
        } elseif ($test_suite->is_for_test_method_with_data_provider()) {
            assert($test_suite instanceof Test_Suite_For_Test_Method_With_Data_Provider);
            $parameters['locationHint'] = sprintf('php_qn://%s::\%s', $test_suite->file(), $test_suite->name());
            $parameters['name'] = $test_suite->method_name();
        }
        $this->write_message('testSuiteStarted', $parameters);
    }
    public function test_suite_finished(Test_Suite_Finished $event): void
    {
        $test_suite = $event->test_suite();
        $parameters = ['name' => $test_suite->name()];
        if ($test_suite->is_for_test_method_with_data_provider()) {
            assert($test_suite instanceof Test_Suite_For_Test_Method_With_Data_Provider);
            $parameters['name'] = $test_suite->method_name();
        }
        $this->write_message('testSuiteFinished', $parameters);
    }
    public function test_prepared(Prepared $event): void
    {
        $test = $event->test();
        $parameters = ['name' => $test->name()];
        if ($test->is_test_method()) {
            assert($test instanceof Test_Method);
            $parameters['locationHint'] = sprintf('php_qn://%s::\%s::%s', $test->file(), $test->class_name(), $test->name());
        }
        $this->write_message('testStarted', $parameters);
        $this->time = $event->telemetry_info()->time();
    }
    /**
     * @throws InvalidArgumentException
     */
    public function test_marked_incomplete(Marked_Incomplete $event): void
    {
        if ($this->time === null) {
            // @codeCoverageIgnoreStart
            $this->time = $event->telemetry_info()->time();
            // @codeCoverageIgnoreEnd
        }
        $this->write_message('testIgnored', ['name' => $event->test()->name(), 'message' => $event->throwable()->message(), 'details' => $this->details($event->throwable()), 'duration' => $this->duration($event)]);
    }
    /**
     * @throws InvalidArgumentException
     */
    public function test_skipped(Skipped $event): void
    {
        if ($this->time === null) {
            $this->time = $event->telemetry_info()->time();
        }
        $parameters = ['name' => $event->test()->name(), 'message' => $event->message()];
        $parameters['duration'] = $this->duration($event);
        $this->write_message('testIgnored', $parameters);
    }
    /**
     * @throws InvalidArgumentException
     */
    public function test_suite_skipped(Test_Suite_Skipped $event): void
    {
        if ($this->time === null) {
            $this->time = $event->telemetry_info()->time();
        }
        $parameters = ['name' => $event->test_suite()->name(), 'message' => $event->message()];
        $parameters['duration'] = $this->duration($event);
        $this->write_message('testIgnored', $parameters);
        $this->write_message('testSuiteFinished', $parameters);
    }
    /**
     * @throws InvalidArgumentException
     */
    public function before_first_test_method_errored(Before_First_Test_Method_Errored $event): void
    {
        if ($this->time === null) {
            $this->time = $event->telemetry_info()->time();
        }
        $parameters = ['name' => $event->test_class_name(), 'message' => $this->message($event->throwable()), 'details' => $this->details($event->throwable()), 'duration' => $this->duration($event)];
        $this->write_message('testFailed', $parameters);
        $this->write_message('testSuiteFinished', $parameters);
    }
    /**
     * @throws InvalidArgumentException
     */
    public function test_errored(Errored $event): void
    {
        if ($this->time === null) {
            $this->time = $event->telemetry_info()->time();
        }
        $this->write_message('testFailed', ['name' => $event->test()->name(), 'message' => $this->message($event->throwable()), 'details' => $this->details($event->throwable()), 'duration' => $this->duration($event)]);
    }
    /**
     * @throws InvalidArgumentException
     */
    public function test_failed(Failed $event): void
    {
        if ($this->time === null) {
            // @codeCoverageIgnoreStart
            $this->time = $event->telemetry_info()->time();
            // @codeCoverageIgnoreEnd
        }
        $parameters = ['name' => $event->test()->name(), 'message' => $this->message($event->throwable()), 'details' => $this->details($event->throwable()), 'duration' => $this->duration($event)];
        if ($event->has_comparison_failure()) {
            $parameters['type'] = 'comparisonFailure';
            $parameters['actual'] = $event->comparison_failure()->actual();
            $parameters['expected'] = $event->comparison_failure()->expected();
        }
        $this->write_message('testFailed', $parameters);
    }
    /**
     * @throws InvalidArgumentException
     */
    public function test_considered_risky(Considered_Risky $event): void
    {
        if ($this->time === null) {
            // @codeCoverageIgnoreStart
            $this->time = $event->telemetry_info()->time();
            // @codeCoverageIgnoreEnd
        }
        $this->write_message('testFailed', ['name' => $event->test()->name(), 'message' => $event->message(), 'details' => '', 'duration' => $this->duration($event)]);
    }
    /**
     * @throws InvalidArgumentException
     */
    public function test_finished(Finished $event): void
    {
        $this->write_message('testFinished', ['name' => $event->test()->name(), 'duration' => $this->duration($event)]);
        $this->time = null;
    }
    public function flush(): void
    {
        $this->printer->flush();
    }
    private function register_subscribers(Facade $facade): void
    {
        $facade->register_subscribers(new Test_Suite_Started_Subscriber($this), new Test_Suite_Finished_Subscriber($this), new Test_Prepared_Subscriber($this), new Test_Finished_Subscriber($this), new Test_Errored_Subscriber($this), new Test_Failed_Subscriber($this), new Test_Marked_Incomplete_Subscriber($this), new Test_Skipped_Subscriber($this), new Test_Suite_Skipped_Subscriber($this), new Test_Considered_Risky_Subscriber($this), new Test_Runner_Execution_Finished_Subscriber($this), new Test_Suite_Before_First_Test_Method_Errored_Subscriber($this));
    }
    private function set_flow_id(): void
    {
        if (stripos(ini_get('disable_functions'), 'getmypid') === false) {
            $this->flow_id = getmypid();
        }
    }
    /**
     * @param array<non-empty-string, int|string> $parameters
     */
    private function write_message(string $event_name, array $parameters = []): void
    {
        $this->printer->print(sprintf('##teamcity[%s', $event_name));
        if ($this->flow_id !== null) {
            $parameters['flowId'] = $this->flow_id;
        }
        foreach ($parameters as $key => $value) {
            $this->printer->print(sprintf(" %s='%s'", $key, $this->escape((string) $value)));
        }
        $this->printer->print("]\n");
    }
    /**
     * @throws InvalidArgumentException
     */
    private function duration(Event $event): int
    {
        if ($this->time === null) {
            // @codeCoverageIgnoreStart
            return 0;
            // @codeCoverageIgnoreEnd
        }
        return (int) round($event->telemetry_info()->time()->duration($this->time)->as_float() * 1000);
    }
    private function escape(string $string): string
    {
        return str_replace(['|', "'", "\n", "\r", ']', '['], ['||', "|'", '|n', '|r', '|]', '|['], $string);
    }
    private function message(Throwable $throwable): string
    {
        if (is_a($throwable->class_name(), Framework_Exception::class, true)) {
            return $throwable->message();
        }
        $buffer = $throwable->class_name();
        if ($throwable->message() !== '') {
            $buffer .= ': ' . $throwable->message();
        }
        return $buffer;
    }
    private function details(Throwable $throwable): string
    {
        $buffer = $throwable->stack_trace();
        while ($throwable->has_previous()) {
            $throwable = $throwable->previous();
            $buffer .= sprintf("\nCaused by\n%s\n%s", $throwable->description(), $throwable->stack_trace());
        }
        return $buffer;
    }
}