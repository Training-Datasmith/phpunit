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
namespace Php_Unit\Util\PHP;

use Php_Unit\Event\Facade;
use Php_Unit\Framework\Child_Process_Result_Processor;
use Php_Unit\Framework\Test;
use Php_Unit\Runner\Code_Coverage;
use Php_Unit\Test_Runner\Test_Result\Passed_Tests;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Job_Runner_Registry
{
    private static ?Job_Runner $runner = null;
    public static function run(Job $job): Result
    {
        return self::runner()->run($job);
    }
    /**
     * @param non-empty-string $processResultFile
     */
    public static function run_test_job(Job $job, string $process_result_file, Test $test): void
    {
        self::runner()->run_test_job($job, $process_result_file, $test);
    }
    public static function set(Job_Runner $runner): void
    {
        self::$runner = $runner;
    }
    private static function runner(): Job_Runner
    {
        if (self::$runner === null) {
            self::$runner = new Default_Job_Runner(new Child_Process_Result_Processor(Facade::instance(), Facade::emitter(), Passed_Tests::instance(), Code_Coverage::instance()));
        }
        return self::$runner;
    }
}