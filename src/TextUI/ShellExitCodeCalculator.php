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

use Php_Unit\Test_Runner\Test_Result\Test_Result;
use Php_Unit\Text_Ui\Configuration\Configuration;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Shell_Exit_Code_Calculator
{
    private const int SUCCESS_EXIT = 0;
    private const int FAILURE_EXIT = 1;
    private const int EXCEPTION_EXIT = 2;
    public function calculate(Configuration $configuration, Test_Result $result): int
    {
        $fail_on_deprecation = false;
        $fail_on_phpunit_deprecation = false;
        $fail_on_phpunit_notice = false;
        $fail_on_phpunit_warning = false;
        $fail_on_empty_test_suite = false;
        $fail_on_incomplete = false;
        $fail_on_notice = false;
        $fail_on_risky = false;
        $fail_on_skipped = false;
        $fail_on_warning = false;
        if ($configuration->fail_on_all_issues()) {
            $fail_on_deprecation = true;
            $fail_on_phpunit_deprecation = true;
            $fail_on_phpunit_notice = true;
            $fail_on_phpunit_warning = true;
            $fail_on_empty_test_suite = true;
            $fail_on_incomplete = true;
            $fail_on_notice = true;
            $fail_on_risky = true;
            $fail_on_skipped = true;
            $fail_on_warning = true;
        }
        if ($configuration->fail_on_deprecation()) {
            $fail_on_deprecation = true;
        }
        if ($configuration->do_not_fail_on_deprecation()) {
            $fail_on_deprecation = false;
        }
        if ($configuration->fail_on_phpunit_deprecation()) {
            $fail_on_phpunit_deprecation = true;
        }
        if ($configuration->do_not_fail_on_phpunit_deprecation()) {
            $fail_on_phpunit_deprecation = false;
        }
        if ($configuration->fail_on_phpunit_notice()) {
            $fail_on_phpunit_notice = true;
        }
        if ($configuration->do_not_fail_on_phpunit_notice()) {
            $fail_on_phpunit_notice = false;
        }
        if ($configuration->fail_on_phpunit_warning()) {
            $fail_on_phpunit_warning = true;
        }
        if ($configuration->do_not_fail_on_phpunit_warning()) {
            $fail_on_phpunit_warning = false;
        }
        if ($configuration->fail_on_empty_test_suite()) {
            $fail_on_empty_test_suite = true;
        }
        if ($configuration->do_not_fail_on_empty_test_suite()) {
            $fail_on_empty_test_suite = false;
        }
        if ($configuration->fail_on_incomplete()) {
            $fail_on_incomplete = true;
        }
        if ($configuration->do_not_fail_on_incomplete()) {
            $fail_on_incomplete = false;
        }
        if ($configuration->fail_on_notice()) {
            $fail_on_notice = true;
        }
        if ($configuration->do_not_fail_on_notice()) {
            $fail_on_notice = false;
        }
        if ($configuration->fail_on_risky()) {
            $fail_on_risky = true;
        }
        if ($configuration->do_not_fail_on_risky()) {
            $fail_on_risky = false;
        }
        if ($configuration->fail_on_skipped()) {
            $fail_on_skipped = true;
        }
        if ($configuration->do_not_fail_on_skipped()) {
            $fail_on_skipped = false;
        }
        if ($configuration->fail_on_warning()) {
            $fail_on_warning = true;
        }
        if ($configuration->do_not_fail_on_warning()) {
            $fail_on_warning = false;
        }
        if ($result->was_successful()) {
            return self::SUCCESS_EXIT;
        }
        if ($fail_on_empty_test_suite && !$result->has_tests()) {
            return self::FAILURE_EXIT;
        }
        if ($fail_on_deprecation && $result->has_php_or_user_deprecations()) {
            return self::FAILURE_EXIT;
        }
        if ($fail_on_phpunit_deprecation && $result->has_phpunit_deprecations()) {
            return self::FAILURE_EXIT;
        }
        if ($fail_on_phpunit_notice && $result->has_phpunit_notices()) {
            return self::FAILURE_EXIT;
        }
        if ($fail_on_phpunit_warning && $result->has_phpunit_warnings()) {
            return self::FAILURE_EXIT;
        }
        if ($fail_on_incomplete && $result->has_incomplete_tests()) {
            return self::FAILURE_EXIT;
        }
        if ($fail_on_notice && $result->has_notices()) {
            return self::FAILURE_EXIT;
        }
        if ($fail_on_risky && $result->has_risky_tests()) {
            return self::FAILURE_EXIT;
        }
        if ($fail_on_skipped && $result->has_skipped_tests()) {
            return self::FAILURE_EXIT;
        }
        if ($fail_on_warning && $result->has_warnings()) {
            return self::FAILURE_EXIT;
        }
        if ($result->has_errors()) {
            return self::EXCEPTION_EXIT;
        }
        return self::FAILURE_EXIT;
    }
}