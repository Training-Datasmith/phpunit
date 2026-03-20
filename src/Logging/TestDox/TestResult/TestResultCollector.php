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
namespace Php_Unit\Logging\Test_Dox;

use function array_merge;
use function assert;
use function is_subclass_of;
use function ksort;
use Php_Unit\Event\Code\Test_Method;
use Php_Unit\Event\Code\Throwable;
use Php_Unit\Event\Facade;
use Php_Unit\Event\InvalidArgumentException;
use Php_Unit\Event\Test\Considered_Risky;
use Php_Unit\Event\Test\Deprecation_Triggered;
use Php_Unit\Event\Test\Errored;
use Php_Unit\Event\Test\Failed;
use Php_Unit\Event\Test\Finished;
use Php_Unit\Event\Test\Marked_Incomplete;
use Php_Unit\Event\Test\Notice_Triggered;
use Php_Unit\Event\Test\Passed;
use Php_Unit\Event\Test\Php_Deprecation_Triggered;
use Php_Unit\Event\Test\Php_Notice_Triggered;
use Php_Unit\Event\Test\Phpunit_Deprecation_Triggered;
use Php_Unit\Event\Test\Phpunit_Error_Triggered;
use Php_Unit\Event\Test\Phpunit_Warning_Triggered;
use Php_Unit\Event\Test\Php_Warning_Triggered;
use Php_Unit\Event\Test\Prepared;
use Php_Unit\Event\Test\Skipped;
use Php_Unit\Event\Test\Warning_Triggered;
use Php_Unit\Framework\Test_Status\Test_Status;
use Php_Unit\Logging\Test_Dox\Test_Result as TestDoxTestMethod;
use Php_Unit\Test_Runner\Issue_Filter;
use ReflectionMethod;
use function uksort;
use function usort;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Test_Result_Collector
{
    /**
     * @var array<string, list<TestDoxTestMethod>>
     */
    private array $tests = [];
    private ?Test_Status $status = null;
    private ?Throwable $throwable = null;
    private bool $prepared = false;
    public function __construct(Facade $facade, private readonly Issue_Filter $issue_filter)
    {
        $this->register_subscribers($facade);
    }
    /**
     * @return array<string, TestResultCollection>
     */
    public function test_methods_grouped_by_class(): array
    {
        $result = [];
        foreach ($this->tests as $prettified_class_name => $tests) {
            $tests_by_declaring_class = [];
            foreach ($tests as $test) {
                $declaring_class_name = (new ReflectionMethod($test->test()->class_name(), $test->test()->method_name()))->get_declaring_class()->get_name();
                if (!isset($tests_by_declaring_class[$declaring_class_name])) {
                    $tests_by_declaring_class[$declaring_class_name] = [];
                }
                $tests_by_declaring_class[$declaring_class_name][] = $test;
            }
            foreach ($tests_by_declaring_class as $declaring_class_name) {
                usort($declaring_class_name, static fn(Test_Dox_Test_Method $a, Test_Dox_Test_Method $b): int => $a->test()->line() <=> $b->test()->line());
            }
            uksort(
                $tests_by_declaring_class,
                /**
                 * @param class-string $a
                 * @param class-string $b
                 */
                static function (string $a, string $b): int {
                    if (is_subclass_of($b, $a)) {
                        return -1;
                    }
                    if (is_subclass_of($a, $b)) {
                        return 1;
                    }
                    return 0;
                }
            );
            $tests = [];
            foreach ($tests_by_declaring_class as $_tests) {
                $tests = array_merge($tests, $_tests);
            }
            $result[$prettified_class_name] = Test_Result_Collection::from_array($tests);
        }
        ksort($result);
        return $result;
    }
    public function test_prepared(Prepared $event): void
    {
        if (!$event->test()->is_test_method()) {
            return;
        }
        $this->status = Test_Status::unknown();
        $this->throwable = null;
        $this->prepared = true;
    }
    public function test_errored(Errored $event): void
    {
        if (!$event->test()->is_test_method()) {
            return;
        }
        $this->status = Test_Status::error($event->throwable()->message());
        $this->throwable = $event->throwable();
        if (!$this->prepared) {
            $test = $event->test();
            assert($test instanceof Test_Method);
            $this->process($test);
        }
    }
    public function test_failed(Failed $event): void
    {
        if (!$event->test()->is_test_method()) {
            return;
        }
        $this->status = Test_Status::failure($event->throwable()->message());
        $this->throwable = $event->throwable();
    }
    public function test_passed(Passed $event): void
    {
        if (!$event->test()->is_test_method()) {
            return;
        }
        $this->update_test_status(Test_Status::success());
    }
    public function test_skipped(Skipped $event): void
    {
        if (!$event->test()->is_test_method()) {
            return;
        }
        $this->update_test_status(Test_Status::skipped($event->message()));
    }
    public function test_marked_incomplete(Marked_Incomplete $event): void
    {
        if (!$event->test()->is_test_method()) {
            return;
        }
        $this->update_test_status(Test_Status::incomplete($event->throwable()->message()));
        $this->throwable = $event->throwable();
    }
    public function test_considered_risky(Considered_Risky $event): void
    {
        if (!$event->test()->is_test_method()) {
            return;
        }
        $this->update_test_status(Test_Status::risky());
    }
    public function test_triggered_deprecation(Deprecation_Triggered $event): void
    {
        if (!$this->issue_filter->should_be_processed($event, true)) {
            return;
        }
        if ($event->ignored_by_baseline()) {
            return;
        }
        $this->update_test_status(Test_Status::deprecation());
    }
    public function test_triggered_notice(Notice_Triggered $event): void
    {
        if (!$this->issue_filter->should_be_processed($event, true)) {
            return;
        }
        if ($event->ignored_by_baseline()) {
            return;
        }
        $this->update_test_status(Test_Status::notice());
    }
    public function test_triggered_warning(Warning_Triggered $event): void
    {
        if (!$this->issue_filter->should_be_processed($event, true)) {
            return;
        }
        if ($event->ignored_by_baseline()) {
            return;
        }
        $this->update_test_status(Test_Status::warning());
    }
    public function test_triggered_php_deprecation(Php_Deprecation_Triggered $event): void
    {
        if (!$this->issue_filter->should_be_processed($event, true)) {
            return;
        }
        if ($event->ignored_by_baseline()) {
            return;
        }
        $this->update_test_status(Test_Status::deprecation());
    }
    public function test_triggered_php_notice(Php_Notice_Triggered $event): void
    {
        if (!$this->issue_filter->should_be_processed($event, true)) {
            return;
        }
        if ($event->ignored_by_baseline()) {
            return;
        }
        $this->update_test_status(Test_Status::notice());
    }
    public function test_triggered_php_warning(Php_Warning_Triggered $event): void
    {
        if (!$this->issue_filter->should_be_processed($event, true)) {
            return;
        }
        if ($event->ignored_by_baseline()) {
            return;
        }
        $this->update_test_status(Test_Status::warning());
    }
    public function test_triggered_phpunit_deprecation(Phpunit_Deprecation_Triggered $event): void
    {
        if (!$event->test()->is_test_method()) {
            return;
        }
        $this->update_test_status(Test_Status::deprecation());
    }
    public function test_triggered_phpunit_error(Phpunit_Error_Triggered $event): void
    {
        if (!$event->test()->is_test_method()) {
            return;
        }
        $this->update_test_status(Test_Status::error());
    }
    public function test_triggered_phpunit_warning(Phpunit_Warning_Triggered $event): void
    {
        if (!$event->test()->is_test_method()) {
            return;
        }
        if ($event->ignored_by_test()) {
            return;
        }
        $this->update_test_status(Test_Status::warning());
    }
    /**
     * @throws InvalidArgumentException
     */
    public function test_finished(Finished $event): void
    {
        if (!$event->test()->is_test_method()) {
            return;
        }
        $test = $event->test();
        assert($test instanceof Test_Method);
        $this->process($test);
        $this->status = null;
        $this->throwable = null;
        $this->prepared = false;
    }
    private function register_subscribers(Facade $facade): void
    {
        $facade->register_subscribers(new Test_Considered_Risky_Subscriber($this), new Test_Errored_Subscriber($this), new Test_Failed_Subscriber($this), new Test_Finished_Subscriber($this), new Test_Marked_Incomplete_Subscriber($this), new Test_Passed_Subscriber($this), new Test_Prepared_Subscriber($this), new Test_Skipped_Subscriber($this), new Test_Triggered_Deprecation_Subscriber($this), new Test_Triggered_Notice_Subscriber($this), new Test_Triggered_Php_Deprecation_Subscriber($this), new Test_Triggered_Php_Notice_Subscriber($this), new Test_Triggered_Phpunit_Deprecation_Subscriber($this), new Test_Triggered_Phpunit_Error_Subscriber($this), new Test_Triggered_Phpunit_Warning_Subscriber($this), new Test_Triggered_Php_Warning_Subscriber($this), new Test_Triggered_Warning_Subscriber($this));
    }
    private function update_test_status(Test_Status $status): void
    {
        if ($this->status !== null && $this->status->is_more_important_than($status)) {
            return;
        }
        $this->status = $status;
    }
    private function process(Test_Method $test): void
    {
        if (!isset($this->tests[$test->test_dox()->prettified_class_name()])) {
            $this->tests[$test->test_dox()->prettified_class_name()] = [];
        }
        $this->tests[$test->test_dox()->prettified_class_name()][] = new Test_Dox_Test_Method($test, $this->status, $this->throwable);
    }
}