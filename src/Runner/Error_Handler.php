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
namespace Php_Unit\Runner;

use function array_keys;
use function array_unshift;
use function array_values;
use function assert;
use function count;
use function debug_backtrace;
use const DEBUG_BACKTRACE_IGNORE_ARGS;
use function defined;
use const E_COMPILE_ERROR;
use const E_COMPILE_WARNING;
use const E_CORE_ERROR;
use const E_CORE_WARNING;
use const E_DEPRECATED;
use const E_ERROR;
use const E_NOTICE;
use const E_PARSE;
use const E_RECOVERABLE_ERROR;
use const E_USER_DEPRECATED;
use const E_USER_ERROR;
use const E_USER_NOTICE;
use const E_USER_WARNING;
use const E_WARNING;
use function error_reporting;
use Php_Unit\Event;
use Php_Unit\Event\Code\Issue_Trigger\Code;
use Php_Unit\Event\Code\Issue_Trigger\Issue_Trigger;
use Php_Unit\Event\Code\No_Test_Case_Object_On_Call_Stack_Exception;
use Php_Unit\Event\Code\Test_Method;
use Php_Unit\Framework\Test_Case;
use Php_Unit\Metadata\Ignore_Deprecations;
use Php_Unit\Metadata\Parser\Registry as MetadataParserRegistry;
use Php_Unit\Runner\Baseline\Baseline;
use Php_Unit\Runner\Baseline\Issue;
use Php_Unit\Runner\Issue_Trigger_Resolver\Default_Resolver as DefaultIssueTriggerResolver;
use Php_Unit\Runner\Issue_Trigger_Resolver\Resolver as IssueTriggerResolver;
use Php_Unit\Text_Ui\Configuration\Registry as ConfigurationRegistry;
use Php_Unit\Text_Ui\Configuration\Source_Filter;
use Php_Unit\Util\Exclude_List;
use function preg_match;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Error_Handler
{
    private const int UNHANDLEABLE_LEVELS = E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING;
    private const int INSUPPRESSIBLE_LEVELS = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;
    private static ?self $instance = null;
    private ?Baseline $baseline = null;
    private readonly Exclude_List $exclude_list;
    private bool $enabled = false;
    private ?int $original_error_reporting_level = null;
    /**
     * @var list<array{int, string, string, int}>
     */
    private array $global_deprecations = [];
    /**
     * @var array<string, list<array{int, string, string, int}>>
     */
    private array $test_case_context_deprecations = [];
    private ?string $test_case_context = null;
    /**
     * @var ?array{functions: list<non-empty-string>, methods: list<array{className: class-string, methodName: non-empty-string}>}
     */
    private ?array $deprecation_triggers = null;
    /**
     * @var non-empty-list<IssueTriggerResolver>
     */
    private array $issue_trigger_resolvers;
    public static function instance(): self
    {
        $source = Configuration_Registry::get()->source();
        $identify_issue_trigger = true;
        if (!$source->identify_issue_trigger()) {
            $identify_issue_trigger = false;
        }
        if (!$source->not_empty()) {
            $identify_issue_trigger = false;
        }
        return self::$instance ?? self::$instance = new self($identify_issue_trigger);
    }
    private function __construct(private readonly bool $identify_issue_trigger)
    {
        $this->exclude_list = new Exclude_List();
        $this->issue_trigger_resolvers = [new Default_Issue_Trigger_Resolver()];
    }
    /**
     * @throws NoTestCaseObjectOnCallStackException
     */
    public function __invoke(int $error_number, string $error_string, string $error_file, int $error_line): false
    {
        $suppressed = (error_reporting() & ~self::INSUPPRESSIBLE_LEVELS) === 0;
        if ($suppressed && $this->exclude_list->is_excluded($error_file)) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }
        /**
         * E_STRICT is deprecated since PHP 8.4.
         *
         * @see https://github.com/sebastianbergmann/phpunit/issues/5956
         */
        if (defined('E_STRICT') && $error_number === 2048) {
            // @codeCoverageIgnoreStart
            $error_number = E_NOTICE;
            // @codeCoverageIgnoreEnd
        }
        $test = Event\Code\Test_Method_Builder::from_call_stack();
        if ($error_number === E_USER_DEPRECATED) {
            $deprecation_frame = $this->guess_deprecation_frame();
            $error_file = $deprecation_frame['file'] ?? $error_file;
            $error_line = $deprecation_frame['line'] ?? $error_line;
        }
        $ignored_by_baseline = $this->ignored_by_baseline($error_file, $error_line, $error_string);
        $ignored_by_test = $this->deprecation_ignored_by_test($test, $error_string);
        switch ($error_number) {
            case E_NOTICE:
                Event\Facade::emitter()->test_triggered_php_notice($test, $error_string, $error_file, $error_line, $suppressed, $ignored_by_baseline);
                break;
            case E_USER_NOTICE:
                Event\Facade::emitter()->test_triggered_notice($test, $error_string, $error_file, $error_line, $suppressed, $ignored_by_baseline);
                break;
            case E_WARNING:
                Event\Facade::emitter()->test_triggered_php_warning($test, $error_string, $error_file, $error_line, $suppressed, $ignored_by_baseline);
                break;
            case E_USER_WARNING:
                Event\Facade::emitter()->test_triggered_warning($test, $error_string, $error_file, $error_line, $suppressed, $ignored_by_baseline);
                break;
            case E_DEPRECATED:
                Event\Facade::emitter()->test_triggered_php_deprecation($test, $error_string, $error_file, $error_line, $suppressed, $ignored_by_baseline, $ignored_by_test, $this->trigger($test, false, $error_string, $error_file));
                break;
            case E_USER_DEPRECATED:
                Event\Facade::emitter()->test_triggered_deprecation($test, $error_string, $error_file, $error_line, $suppressed, $ignored_by_baseline, $ignored_by_test, $this->trigger($test, true, $error_string), $this->stack_trace());
                break;
            case E_USER_ERROR:
                Event\Facade::emitter()->test_triggered_error($test, $error_string, $error_file, $error_line, $suppressed);
                throw new ErrorException('E_USER_ERROR was triggered');
            default:
                return false;
        }
        return false;
    }
    public function deprecation_handler(int $error_number, string $error_string, string $error_file, int $error_line): true
    {
        if ($this->test_case_context !== null) {
            $this->test_case_context_deprecations[$this->test_case_context][] = [$error_number, $error_string, $error_file, $error_line];
        } else {
            $this->global_deprecations[] = [$error_number, $error_string, $error_file, $error_line];
        }
        return true;
    }
    public function register_deprecation_handler(): void
    {
        set_error_handler([self::$instance, 'deprecationHandler'], E_USER_DEPRECATED | E_DEPRECATED);
    }
    public function restore_deprecation_handler(): void
    {
        restore_error_handler();
    }
    public function enable(Test_Case $test): void
    {
        assert(!$this->enabled);
        $old_error_handler = set_error_handler($this);
        if ($old_error_handler !== null) {
            restore_error_handler();
            return;
        }
        $this->enabled = true;
        $this->original_error_reporting_level = error_reporting();
        $this->trigger_global_deprecations($test);
        error_reporting($this->original_error_reporting_level & self::UNHANDLEABLE_LEVELS);
    }
    public function disable(): void
    {
        if (!$this->enabled) {
            return;
        }
        restore_error_handler();
        error_reporting(error_reporting() | $this->original_error_reporting_level);
        $this->enabled = false;
        $this->original_error_reporting_level = null;
    }
    public function use_baseline(Baseline $baseline): void
    {
        $this->baseline = $baseline;
    }
    /**
     * @param array{functions: list<non-empty-string>, methods: list<array{className: class-string, methodName: non-empty-string}>} $deprecationTriggers
     */
    public function use_deprecation_triggers(array $deprecation_triggers): void
    {
        $this->deprecation_triggers = $deprecation_triggers;
    }
    public function add_issue_trigger_resolver(Issue_Trigger_Resolver $resolver): void
    {
        array_unshift($this->issue_trigger_resolvers, $resolver);
    }
    public function enter_test_case_context(string $class_name, string $method_name): void
    {
        $this->test_case_context = $this->test_case_context($class_name, $method_name);
    }
    public function leave_test_case_context(): void
    {
        $this->test_case_context = null;
    }
    /**
     * @param non-empty-string $file
     * @param positive-int     $line
     * @param non-empty-string $description
     */
    private function ignored_by_baseline(string $file, int $line, string $description): bool
    {
        if ($this->baseline === null) {
            return false;
        }
        return $this->baseline->has(Issue::from($file, $line, null, $description));
    }
    /**
     * @param null|non-empty-string $errorFile
     */
    private function trigger(Test_Method $test, bool $is_userland, string $error_string, ?string $error_file = null): Issue_Trigger
    {
        if (!$this->identify_issue_trigger) {
            return Issue_Trigger::from(null, null);
        }
        if (!$is_userland) {
            assert($error_file !== null);
            return Issue_Trigger::from(Code::PHP, $this->categorize_file($error_file, $test));
        }
        $trace = $this->filtered_stack_trace();
        return $this->trigger_for_userland_deprecation($test, $error_string, $trace);
    }
    /**
     * @param list<array{file?: string, line?: int, class?: class-string, function?: string, type?: string, args?: list<mixed>}> $trace
     */
    private function trigger_for_userland_deprecation(Test_Method $test, string $message, array $trace): Issue_Trigger
    {
        foreach ($this->issue_trigger_resolvers as $resolver) {
            $result = $resolver->resolve($trace, $message);
            if ($result === null) {
                continue;
            }
            $callee = null;
            if ($result->has_callee()) {
                $callee = $this->categorize_file($result->callee(), $test);
            }
            $caller = null;
            if ($result->has_caller()) {
                $caller = $this->categorize_file($result->caller(), $test);
            }
            return Issue_Trigger::from($callee, $caller);
        }
        // @codeCoverageIgnoreStart
        return Issue_Trigger::from(null, null);
        // @codeCoverageIgnoreEnd
    }
    /**
     * @param non-empty-string $file
     */
    private function categorize_file(string $file, Test_Method $test): Code
    {
        if ($file === $test->file()) {
            return Code::Test;
        }
        if (Source_Filter::instance()->includes($file)) {
            return Code::FirstParty;
        }
        if ($this->exclude_list->is_excluded($file)) {
            return Code::PHPUnit;
        }
        return Code::ThirdParty;
    }
    /**
     * @return list<array{file: string, line: int, class?: string, function?: string, type: string, args?: list<mixed>}>
     */
    private function filtered_stack_trace(): array
    {
        $ignore_arguments = count($this->issue_trigger_resolvers) === 1;
        $trace = $this->error_stack_trace($ignore_arguments);
        if ($this->deprecation_triggers === null) {
            return array_values($trace);
        }
        foreach (array_keys($trace) as $frame) {
            foreach ($this->deprecation_triggers['functions'] as $function) {
                if ($this->frame_is_function($trace[$frame], $function)) {
                    unset($trace[$frame]);
                    continue 2;
                }
            }
            foreach ($this->deprecation_triggers['methods'] as $method) {
                if ($this->frame_is_method($trace[$frame], $method)) {
                    unset($trace[$frame]);
                    continue 2;
                }
            }
        }
        return array_values($trace);
    }
    /**
     * @return ?array{file: non-empty-string, line: positive-int}
     */
    private function guess_deprecation_frame(): ?array
    {
        if ($this->deprecation_triggers === null) {
            return null;
        }
        $trace = $this->error_stack_trace();
        foreach ($trace as $frame) {
            foreach ($this->deprecation_triggers['functions'] as $function) {
                if ($this->frame_is_function($frame, $function)) {
                    return $frame;
                }
            }
            foreach ($this->deprecation_triggers['methods'] as $method) {
                if ($this->frame_is_method($frame, $method)) {
                    return $frame;
                }
            }
        }
        return null;
    }
    /**
     * @return list<array{file: string, line: ?int, class?: class-string, function?: string, type: string, args?: list<mixed>}>
     */
    private function error_stack_trace(bool $ignore_args = true): array
    {
        if ($ignore_args) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        } else {
            $trace = debug_backtrace();
        }
        $i = 0;
        do {
            unset($trace[$i]);
        } while (self::class === ($trace[++$i]['class'] ?? null));
        return array_values($trace);
    }
    /**
     * @param array{class? : class-string, function?: non-empty-string} $frame
     * @param non-empty-string                                          $function
     */
    private function frame_is_function(array $frame, string $function): bool
    {
        return !isset($frame['class']) && isset($frame['function']) && $frame['function'] === $function;
    }
    /**
     * @param array{class? : class-string, function?: non-empty-string}    $frame
     * @param array{className: class-string, methodName: non-empty-string} $method
     */
    private function frame_is_method(array $frame, array $method): bool
    {
        return isset($frame['class']) && $frame['class'] === $method['className'] && isset($frame['function']) && $frame['function'] === $method['methodName'];
    }
    /**
     * @return non-empty-string
     */
    private function stack_trace(): string
    {
        $buffer = '';
        foreach ($this->error_stack_trace() as $frame) {
            /**
             * @see https://github.com/sebastianbergmann/phpunit/issues/6043
             */
            if (!isset($frame['file'])) {
                continue;
            }
            if ($this->exclude_list->is_excluded($frame['file'])) {
                continue;
            }
            $buffer .= sprintf("%s:%s\n", $frame['file'], $frame['line'] ?? '?');
        }
        return $buffer;
    }
    private function trigger_global_deprecations(Test_Case $test): void
    {
        foreach ($this->global_deprecations ?? [] as $d) {
            $this->__invoke(...$d);
        }
        $test_case_context = $this->test_case_context($test::class, $test->name());
        foreach ($this->test_case_context_deprecations[$test_case_context] ?? [] as $d) {
            $this->__invoke(...$d);
        }
    }
    private function test_case_context(string $class_name, string $method_name): string
    {
        return "{$class_name}::{$method_name}";
    }
    private function deprecation_ignored_by_test(Test_Method $test, string $message): bool
    {
        $metadata = Metadata_Parser_Registry::parser()->for_class_and_method($test->class_name(), $test->method_name())->is_ignore_deprecations()->as_array();
        foreach ($metadata as $metadatum) {
            assert($metadatum instanceof Ignore_Deprecations);
            $ignore_deprecation_message_pattern = $metadatum->message_pattern();
            if ($ignore_deprecation_message_pattern === null || (bool) preg_match('{' . $ignore_deprecation_message_pattern . '}', $message)) {
                return true;
            }
        }
        return false;
    }
}