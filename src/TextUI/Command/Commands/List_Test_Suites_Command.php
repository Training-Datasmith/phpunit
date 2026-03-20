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
namespace Php_Unit\Text_Ui\Command;

use function assert;
use function count;
use function ksort;
use const PHP_EOL;
use Php_Unit\Framework\Test_Suite;
use Php_Unit\Text_Ui\Configuration\Registry;
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class List_Test_Suites_Command implements Command
{
    public function __construct(private Test_Suite $test_suite)
    {
    }
    public function execute(): Result
    {
        /** @var array<non-empty-string, positive-int> $suites */
        $suites = [];
        foreach ($this->test_suite->tests() as $test) {
            assert($test instanceof Test_Suite);
            $suites[$test->name()] = count($test->collect());
        }
        ksort($suites);
        $buffer = $this->warn_about_conflicting_options();
        $buffer .= sprintf('Available test suite%s:' . PHP_EOL, count($suites) > 1 ? 's' : '');
        foreach ($suites as $suite => $number_of_tests) {
            $buffer .= sprintf(' - %s (%d test%s)' . PHP_EOL, $suite, $number_of_tests, $number_of_tests > 1 ? 's' : '');
        }
        return Result::from($buffer);
    }
    private function warn_about_conflicting_options(): string
    {
        $buffer = '';
        $configuration = Registry::get();
        if ($configuration->include_test_suites() !== [] && !$configuration->has_default_test_suite()) {
            $buffer .= 'The --testsuite and --list-suites options cannot be combined, --testsuite is ignored' . PHP_EOL;
        }
        if ($configuration->has_filter()) {
            $buffer .= 'The --filter and --list-suites options cannot be combined, --filter is ignored' . PHP_EOL;
        }
        if ($configuration->has_groups()) {
            $buffer .= 'The --group (CLI) and <groups> (XML) options cannot be combined with --list-suites, --group and <groups> are ignored' . PHP_EOL;
        }
        if ($configuration->has_exclude_groups()) {
            $buffer .= 'The --exclude-group (CLI) and <groups> (XML) options cannot be combined with --list-suites, --exclude-group and <groups> are ignored' . PHP_EOL;
        }
        if ($buffer !== '') {
            $buffer .= PHP_EOL;
        }
        return $buffer;
    }
}