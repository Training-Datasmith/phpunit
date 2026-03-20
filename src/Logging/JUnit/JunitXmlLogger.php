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
namespace Php_Unit\Logging\J_Unit;

use function assert;
use function basename;
use Dom_Document;
use Dom_Element;
use function is_int;
use const PHP_EOL;
use Php_Unit\Event\Code\Test;
use Php_Unit\Event\Code\Test_Method;
use Php_Unit\Event\Facade;
use Php_Unit\Event\InvalidArgumentException;
use Php_Unit\Event\Telemetry\Hr_Time;
use Php_Unit\Event\Telemetry\Info;
use Php_Unit\Event\Test\Errored;
use Php_Unit\Event\Test\Failed;
use Php_Unit\Event\Test\Finished;
use Php_Unit\Event\Test\Marked_Incomplete;
use Php_Unit\Event\Test\Preparation_Started;
use Php_Unit\Event\Test\Prepared;
use Php_Unit\Event\Test\Printed_Unexpected_Output;
use Php_Unit\Event\Test\Skipped;
use Php_Unit\Event\Test_Suite\Started;
use Php_Unit\Text_Ui\Output\Printer;
use Php_Unit\Util\Xml;
use function sprintf;
use function str_replace;
use function trim;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Junit_Xml_Logger
{
    private Dom_Document $document;
    private Dom_Element $root;
    /**
     * @var array<int, DOMElement>
     */
    private array $test_suites = [];
    /**
     * @var array<int, int>
     */
    private array $test_suite_tests = [0];
    /**
     * @var array<int, int>
     */
    private array $test_suite_assertions = [0];
    /**
     * @var array<int, int>
     */
    private array $test_suite_errors = [0];
    /**
     * @var array<int, int>
     */
    private array $test_suite_failures = [0];
    /**
     * @var array<int, int>
     */
    private array $test_suite_skipped = [0];
    /**
     * @var array<int, float>
     */
    private array $test_suite_times = [0.0];
    private int $test_suite_level = 0;
    private ?Dom_Element $current_test_case = null;
    private ?Hr_Time $time = null;
    private bool $prepared = false;
    private bool $preparation_failed = false;
    private ?string $unexpected_output = null;
    public function __construct(private readonly Printer $printer, Facade $facade)
    {
        $this->register_subscribers($facade);
        $this->create_document();
    }
    public function flush(): void
    {
        $xml = $this->document->save_xml();
        if ($xml === false) {
            $xml = '';
        }
        $this->printer->print($xml);
        $this->printer->flush();
    }
    public function test_suite_started(Started $event): void
    {
        $test_suite = $this->document->create_element('testsuite');
        $test_suite->set_attribute('name', $event->test_suite()->name());
        if ($event->test_suite()->is_for_test_class()) {
            $test_suite->set_attribute('file', $event->test_suite()->file());
        }
        if ($this->test_suite_level > 0) {
            $this->test_suites[$this->test_suite_level]->append_child($test_suite);
        } else {
            $this->root->append_child($test_suite);
        }
        $this->test_suite_level++;
        $this->test_suites[$this->test_suite_level] = $test_suite;
        $this->test_suite_tests[$this->test_suite_level] = 0;
        $this->test_suite_assertions[$this->test_suite_level] = 0;
        $this->test_suite_errors[$this->test_suite_level] = 0;
        $this->test_suite_failures[$this->test_suite_level] = 0;
        $this->test_suite_skipped[$this->test_suite_level] = 0;
        $this->test_suite_times[$this->test_suite_level] = 0.0;
    }
    public function test_suite_finished(): void
    {
        $this->test_suites[$this->test_suite_level]->set_attribute('tests', (string) $this->test_suite_tests[$this->test_suite_level]);
        $this->test_suites[$this->test_suite_level]->set_attribute('assertions', (string) $this->test_suite_assertions[$this->test_suite_level]);
        $this->test_suites[$this->test_suite_level]->set_attribute('errors', (string) $this->test_suite_errors[$this->test_suite_level]);
        $this->test_suites[$this->test_suite_level]->set_attribute('failures', (string) $this->test_suite_failures[$this->test_suite_level]);
        $this->test_suites[$this->test_suite_level]->set_attribute('skipped', (string) $this->test_suite_skipped[$this->test_suite_level]);
        $this->test_suites[$this->test_suite_level]->set_attribute('time', sprintf('%F', $this->test_suite_times[$this->test_suite_level]));
        if ($this->test_suite_level > 1) {
            $this->test_suite_tests[$this->test_suite_level - 1] += $this->test_suite_tests[$this->test_suite_level];
            $this->test_suite_assertions[$this->test_suite_level - 1] += $this->test_suite_assertions[$this->test_suite_level];
            $this->test_suite_errors[$this->test_suite_level - 1] += $this->test_suite_errors[$this->test_suite_level];
            $this->test_suite_failures[$this->test_suite_level - 1] += $this->test_suite_failures[$this->test_suite_level];
            $this->test_suite_skipped[$this->test_suite_level - 1] += $this->test_suite_skipped[$this->test_suite_level];
            $this->test_suite_times[$this->test_suite_level - 1] += $this->test_suite_times[$this->test_suite_level];
        }
        $this->test_suite_level--;
    }
    /**
     * @throws InvalidArgumentException
     */
    public function test_preparation_started(Preparation_Started $event): void
    {
        $this->create_test_case($event);
        $this->preparation_failed = false;
    }
    public function test_preparation_errored(): void
    {
        $this->preparation_failed = true;
    }
    public function test_preparation_failed(): void
    {
        $this->preparation_failed = true;
    }
    public function test_prepared(): void
    {
        $this->prepared = true;
    }
    public function test_printed_unexpected_output(Printed_Unexpected_Output $event): void
    {
        $this->unexpected_output = $event->output();
    }
    /**
     * @throws InvalidArgumentException
     */
    public function test_finished(Finished $event): void
    {
        if (!$this->prepared || $this->preparation_failed) {
            return;
        }
        $this->handle_finish($event->telemetry_info(), $event->number_of_assertions_performed());
    }
    /**
     * @throws InvalidArgumentException
     */
    public function test_marked_incomplete(Marked_Incomplete $event): void
    {
        $this->handle_incomplete_or_skipped($event);
    }
    /**
     * @throws InvalidArgumentException
     */
    public function test_skipped(Skipped $event): void
    {
        $this->handle_incomplete_or_skipped($event);
    }
    /**
     * @throws InvalidArgumentException
     */
    public function test_errored(Errored $event): void
    {
        $this->handle_fault($event, 'error');
        $this->test_suite_errors[$this->test_suite_level]++;
    }
    /**
     * @throws InvalidArgumentException
     */
    public function test_failed(Failed $event): void
    {
        $this->handle_fault($event, 'failure');
        $this->test_suite_failures[$this->test_suite_level]++;
    }
    /**
     * @throws InvalidArgumentException
     */
    private function handle_finish(Info $telemetry_info, int $number_of_assertions_performed): void
    {
        assert($this->current_test_case !== null);
        assert($this->time !== null);
        $time = $telemetry_info->time()->duration($this->time)->as_float();
        $this->test_suite_assertions[$this->test_suite_level] += $number_of_assertions_performed;
        $this->current_test_case->set_attribute('assertions', (string) $number_of_assertions_performed);
        $this->current_test_case->set_attribute('time', sprintf('%F', $time));
        if ($this->unexpected_output !== null) {
            $system_out = $this->document->create_element('system-out', Xml::prepare_string($this->unexpected_output));
            $this->current_test_case->append_child($system_out);
        }
        $this->test_suites[$this->test_suite_level]->append_child($this->current_test_case);
        $this->test_suite_tests[$this->test_suite_level]++;
        $this->test_suite_times[$this->test_suite_level] += $time;
        $this->current_test_case = null;
        $this->time = null;
        $this->preparation_failed = false;
        $this->prepared = false;
        $this->unexpected_output = null;
    }
    private function register_subscribers(Facade $facade): void
    {
        $facade->register_subscribers(new Test_Suite_Started_Subscriber($this), new Test_Suite_Finished_Subscriber($this), new Test_Preparation_Started_Subscriber($this), new Test_Preparation_Errored_Subscriber($this), new Test_Preparation_Failed_Subscriber($this), new Test_Prepared_Subscriber($this), new Test_Printed_Unexpected_Output_Subscriber($this), new Test_Finished_Subscriber($this), new Test_Errored_Subscriber($this), new Test_Failed_Subscriber($this), new Test_Marked_Incomplete_Subscriber($this), new Test_Skipped_Subscriber($this), new Test_Runner_Execution_Finished_Subscriber($this));
    }
    private function create_document(): void
    {
        $this->document = new Dom_Document('1.0', 'UTF-8');
        $this->document->format_output = true;
        $this->root = $this->document->create_element('testsuites');
        $this->document->append_child($this->root);
    }
    /**
     * @throws InvalidArgumentException
     */
    private function handle_fault(Errored|Failed $event, string $type): void
    {
        if (!$this->prepared) {
            $this->create_test_case($event);
        }
        assert($this->current_test_case !== null);
        $buffer = $this->test_as_string($event->test());
        $throwable = $event->throwable();
        $buffer .= trim($throwable->description() . PHP_EOL . $throwable->stack_trace());
        $fault = $this->document->create_element($type, Xml::prepare_string($buffer));
        $fault->set_attribute('type', $throwable->class_name());
        $this->current_test_case->append_child($fault);
        if (!$this->prepared) {
            $this->handle_finish($event->telemetry_info(), 0);
        }
    }
    /**
     * @throws InvalidArgumentException
     */
    private function handle_incomplete_or_skipped(Marked_Incomplete|Skipped $event): void
    {
        if (!$this->prepared) {
            $this->create_test_case($event);
        }
        assert($this->current_test_case !== null);
        $skipped = $this->document->create_element('skipped');
        $this->current_test_case->append_child($skipped);
        $this->test_suite_skipped[$this->test_suite_level]++;
        if (!$this->prepared) {
            $this->handle_finish($event->telemetry_info(), 0);
        }
    }
    /**
     * @throws InvalidArgumentException
     */
    private function test_as_string(Test $test): string
    {
        if ($test->is_phpt()) {
            return basename($test->file());
        }
        assert($test instanceof Test_Method);
        return sprintf('%s::%s%s', $test->class_name(), $this->name($test), PHP_EOL);
    }
    /**
     * @throws InvalidArgumentException
     */
    private function name(Test $test): string
    {
        if ($test->is_phpt()) {
            return basename($test->file());
        }
        assert($test instanceof Test_Method);
        if (!$test->test_data()->has_data_from_data_provider()) {
            return $test->method_name();
        }
        $data_set_name = $test->test_data()->data_from_data_provider()->data_set_name();
        if (is_int($data_set_name)) {
            return sprintf('%s with data set #%d', $test->method_name(), $data_set_name);
        }
        return sprintf('%s with data set "%s"', $test->method_name(), $data_set_name);
    }
    /**
     * @throws InvalidArgumentException
     *
     * @phpstan-assert !null $this->currentTestCase
     */
    private function create_test_case(Errored|Failed|Marked_Incomplete|Preparation_Started|Prepared|Skipped $event): void
    {
        $test_case = $this->document->create_element('testcase');
        $test = $event->test();
        $test_case->set_attribute('name', $this->name($test));
        $test_case->set_attribute('file', $test->file());
        if ($test->is_test_method()) {
            assert($test instanceof Test_Method);
            $test_case->set_attribute('line', (string) $test->line());
            $test_case->set_attribute('class', $test->class_name());
            $test_case->set_attribute('classname', str_replace('\\', '.', $test->class_name()));
        }
        $this->current_test_case = $test_case;
        $this->time = $event->telemetry_info()->time();
    }
}