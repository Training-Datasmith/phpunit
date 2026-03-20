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

use function array_diff_assoc;
use function array_intersect;
use function array_unique;
use function assert;
use AssertionError;
use function extension_loaded;
use const PHP_EOL;
use Php_Unit\Event\Facade;
use Php_Unit\Metadata\Api\Code_Coverage as CodeCoverageMetadataApi;
use Php_Unit\Metadata\Parser\Registry as MetadataRegistry;
use Php_Unit\Runner\Code_Coverage;
use Php_Unit\Runner\Error_Handler;
use Php_Unit\Runner\Exception;
use Php_Unit\Text_Ui\Configuration\Configuration;
use Php_Unit\Text_Ui\Configuration\Registry as ConfigurationRegistry;
use Sebastian_Bergmann\Code_Coverage\Exception as CodeCoverageException;
use Sebastian_Bergmann\Code_Coverage\InvalidArgumentException;
use Sebastian_Bergmann\Code_Coverage\Test\Target\Target_Collection;
use Sebastian_Bergmann\Code_Coverage\Unintentionally_Covered_Code_Exception;
use Sebastian_Bergmann\Invoker\Invoker;
use Sebastian_Bergmann\Invoker\Timeout_Exception;
use function sprintf;
use Throwable;
use function xdebug_is_debugger_active;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Test_Runner
{
    private ?bool $time_limit_can_be_enforced = null;
    private readonly Configuration $configuration;
    public function __construct()
    {
        $this->configuration = Configuration_Registry::get();
    }
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws UnintentionallyCoveredCodeException
     */
    public function run(Test_Case $test): void
    {
        Assert::reset_count();
        $code_coverage_metadata_api = new Code_Coverage_Metadata_Api();
        $covers_targets = $code_coverage_metadata_api->covers_targets($test::class, $test->name());
        $uses_targets = $code_coverage_metadata_api->uses_targets($test::class, $test->name());
        $should_code_coverage_be_collected = $code_coverage_metadata_api->should_code_coverage_be_collected_for($test);
        $this->perform_sanity_checks($test, $covers_targets, $uses_targets, $should_code_coverage_be_collected);
        $error = false;
        $failure = false;
        $incomplete = false;
        $risky = false;
        $skipped = false;
        if ($this->should_error_handler_be_used($test)) {
            Error_Handler::instance()->enable($test);
        }
        $collect_code_coverage = Code_Coverage::instance()->is_active() && $should_code_coverage_be_collected;
        if ($collect_code_coverage) {
            Code_Coverage::instance()->start($test);
        }
        try {
            if ($this->can_time_limit_be_enforced() && $this->should_time_limit_be_enforced($test)) {
                $risky = $this->run_test_with_timeout($test);
            } else {
                $test->run_bare();
            }
        } catch (Assertion_Failed_Error $e) {
            $failure = true;
            if ($e instanceof Incomplete_Test_Error) {
                $incomplete = true;
            } elseif ($e instanceof Skipped_Test) {
                $skipped = true;
            }
        } catch (AssertionError $e) {
            $test->add_to_assertion_count(1);
            $failure = true;
            $frame = $e->get_trace()[0];
            assert(isset($frame['file']));
            assert(isset($frame['line']));
            $e = new Assertion_Failed_Error(sprintf('%s in %s:%s', $e->get_message(), $frame['file'], $frame['line']));
        } catch (Throwable $e) {
            $error = true;
        }
        $test->add_to_assertion_count(Assert::get_count());
        if ($this->configuration->report_useless_tests() && !$test->does_not_perform_assertions() && $test->number_of_assertions_performed() === 0) {
            $risky = true;
        }
        if (!$error && !$failure && !$incomplete && !$skipped && !$risky && $this->configuration->require_coverage_metadata() && !$this->has_coverage_metadata($test::class, $test->name())) {
            Facade::emitter()->test_considered_risky($test->value_object_for_events(), 'This test does not define a code coverage target but is expected to do so');
            $risky = true;
        }
        if ($collect_code_coverage) {
            $append = !$risky && !$incomplete && !$skipped;
            if (!$append) {
                $covers_targets = false;
                $uses_targets = null;
            }
            try {
                Code_Coverage::instance()->stop($append, $covers_targets, $uses_targets);
            } catch (Unintentionally_Covered_Code_Exception $cce) {
                Facade::emitter()->test_considered_risky($test->value_object_for_events(), 'This test executed code that is not listed as code to be covered or used:' . PHP_EOL . $cce->get_message());
            } catch (Code_Coverage_Exception $cce) {
                $error = true;
                $e ??= $cce;
            }
        }
        Error_Handler::instance()->disable();
        if (!$error && !$incomplete && !$skipped && $this->configuration->report_useless_tests() && !$test->does_not_perform_assertions() && $test->number_of_assertions_performed() === 0) {
            Facade::emitter()->test_considered_risky($test->value_object_for_events(), 'This test did not perform any assertions');
        }
        if ($test->does_not_perform_assertions() && $test->number_of_assertions_performed() > 0) {
            Facade::emitter()->test_considered_risky($test->value_object_for_events(), sprintf('This test is not expected to perform assertions but performed %d assertion%s', $test->number_of_assertions_performed(), $test->number_of_assertions_performed() > 1 ? 's' : ''));
        }
        if ($test->has_unexpected_output()) {
            Facade::emitter()->test_printed_unexpected_output($test->output());
        }
        if ($this->configuration->disallow_test_output() && $test->has_unexpected_output()) {
            Facade::emitter()->test_considered_risky($test->value_object_for_events(), sprintf('Test code or tested code printed unexpected output: %s', $test->output()));
        }
        if ($test->was_prepared()) {
            Facade::emitter()->test_finished($test->value_object_for_events(), $test->number_of_assertions_performed());
        }
    }
    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    private function has_coverage_metadata(string $class_name, string $method_name): bool
    {
        foreach (Metadata_Registry::parser()->for_class_and_method($class_name, $method_name) as $metadata) {
            if ($metadata->is_covers_namespace()) {
                return true;
            }
            if ($metadata->is_covers_trait()) {
                return true;
            }
            if ($metadata->is_covers_class()) {
                return true;
            }
            if ($metadata->is_covers_classes_that_extend_class()) {
                return true;
            }
            if ($metadata->is_covers_classes_that_implement_interface()) {
                return true;
            }
            if ($metadata->is_covers_method()) {
                return true;
            }
            if ($metadata->is_covers_function()) {
                return true;
            }
            if ($metadata->is_covers_nothing()) {
                return true;
            }
        }
        return false;
    }
    private function can_time_limit_be_enforced(): bool
    {
        if ($this->time_limit_can_be_enforced !== null) {
            return $this->time_limit_can_be_enforced;
        }
        $this->time_limit_can_be_enforced = (new Invoker())->can_invoke_with_timeout();
        return $this->time_limit_can_be_enforced;
    }
    private function should_time_limit_be_enforced(Test_Case $test): bool
    {
        if (!$this->configuration->enforce_time_limit()) {
            return false;
        }
        if (!($this->configuration->default_time_limit() > 0 || $test->size()->is_known())) {
            return false;
        }
        if (extension_loaded('xdebug') && xdebug_is_debugger_active()) {
            return false;
        }
        return true;
    }
    /**
     * @throws Throwable
     */
    private function run_test_with_timeout(Test_Case $test): bool
    {
        $_timeout = $this->configuration->default_time_limit();
        $test_size = $test->size();
        if ($test_size->is_small()) {
            $_timeout = $this->configuration->timeout_for_small_tests();
        } elseif ($test_size->is_medium()) {
            $_timeout = $this->configuration->timeout_for_medium_tests();
        } elseif ($test_size->is_large()) {
            $_timeout = $this->configuration->timeout_for_large_tests();
        }
        try {
            (new Invoker())->invoke($test->run_bare(...), [], $_timeout);
        } catch (Timeout_Exception) {
            Facade::emitter()->test_considered_risky($test->value_object_for_events(), sprintf('This test was aborted after %d second%s', $_timeout, $_timeout !== 1 ? 's' : ''));
            return true;
        }
        return false;
    }
    private function should_error_handler_be_used(Test_Case $test): bool
    {
        if (Metadata_Registry::parser()->for_method($test::class, $test->name())->is_without_error_handler()->is_not_empty()) {
            return false;
        }
        return true;
    }
    private function perform_sanity_checks(Test_Case $test, Target_Collection $covers_targets, Target_Collection $uses_targets, bool $should_code_coverage_be_collected): void
    {
        if (!$should_code_coverage_be_collected) {
            if ($covers_targets->is_not_empty() || $uses_targets->is_not_empty()) {
                Facade::emitter()->test_triggered_phpunit_warning($test->value_object_for_events(), '#[Covers*] and #[Uses*] attributes do not have an effect when the #[CoversNothing] attribute is used');
            }
        }
        $covers_as_string = [];
        $uses_as_string = [];
        foreach ($covers_targets as $covers_target) {
            $covers_as_string[] = $covers_target->description();
        }
        foreach ($uses_targets as $uses_target) {
            $uses_as_string[] = $uses_target->description();
        }
        $covers_duplicates = array_unique(array_diff_assoc($covers_as_string, array_unique($covers_as_string)));
        $uses_duplicates = array_unique(array_diff_assoc($uses_as_string, array_unique($uses_as_string)));
        $covers_and_uses = array_intersect($covers_as_string, $uses_as_string);
        foreach ($covers_duplicates as $target) {
            Facade::emitter()->test_triggered_phpunit_warning($test->value_object_for_events(), sprintf('%s is targeted multiple times by the same "Covers" attribute', $target));
        }
        foreach ($uses_duplicates as $target) {
            Facade::emitter()->test_triggered_phpunit_warning($test->value_object_for_events(), sprintf('%s is targeted multiple times by the same "Uses" attribute', $target));
        }
        foreach ($covers_and_uses as $target) {
            Facade::emitter()->test_triggered_phpunit_warning($test->value_object_for_events(), sprintf('%s is targeted by both "Covers" and "Uses" attributes', $target));
        }
    }
}